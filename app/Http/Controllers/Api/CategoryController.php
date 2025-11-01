<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\CategoryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function __construct(
        private CategoryService $categoryService
    ) {}

    /**
     * Отримати список категорій користувача (включаючи системні).
     */
    public function index(Request $request): JsonResponse
    {
        $filters = $request->only(['type', 'is_active']);
        
        $categories = $this->categoryService->getUserCategories(
            $request->user()->id,
            $filters
        );

        return response()->json([
            'success' => true,
            'data' => [
                'categories' => $categories,
            ],
        ]);
    }

    /**
     * Створити нову категорію.
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:100',
                'type' => 'required|in:income,expense',
                'icon' => 'nullable|string|max:50',
                'color' => 'nullable|string|size:7|regex:/^#[0-9A-Fa-f]{6}$/',
                'is_active' => 'boolean',
            ]);

            $category = $this->categoryService->createCategory(
                $request->user()->id,
                $validated
            );

            return response()->json([
                'success' => true,
                'message' => 'Категорію успішно створено',
                'data' => [
                    'category' => $category,
                ],
            ], 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Помилка валідації',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            \Log::error('Category creation error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Помилка створення категорії: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Отримати категорію за ID.
     */
    public function show(Request $request, int $id): JsonResponse
    {
        $category = $this->categoryService->getCategoryById($id);

        // Перевіряємо чи це власна або системна категорія
        if ($category->user_id !== null && $category->user_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Доступ заборонено',
            ], 403);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'category' => $category,
            ],
        ]);
    }

    /**
     * Оновити категорію.
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'string|max:100',
            'icon' => 'nullable|string|max:50',
            'color' => 'nullable|string|size:7|regex:/^#[0-9A-Fa-f]{6}$/',
            'is_active' => 'boolean',
        ]);

        try {
            $category = $this->categoryService->updateCategory(
                $id,
                $request->user()->id,
                $validated
            );

            return response()->json([
                'success' => true,
                'message' => 'Категорію успішно оновлено',
                'data' => [
                    'category' => $category,
                ],
            ]);
        } catch (\Exception $e) {
            $statusCode = $e->getCode() === 403 ? 403 : 500;
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], $statusCode);
        }
    }

    /**
     * Видалити категорію.
     */
    public function destroy(Request $request, int $id): JsonResponse
    {
        try {
            $this->categoryService->deleteCategory($id, $request->user()->id);

            return response()->json([
                'success' => true,
                'message' => 'Категорію успішно видалено',
            ]);
        } catch (\Exception $e) {
            $statusCode = $e->getCode() === 403 ? 403 : 500;
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], $statusCode);
        }
    }
}
