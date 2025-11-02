<?php

namespace App\Repositories;

use App\Models\Category;
use App\Repositories\Interfaces\CategoryRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class CategoryRepository implements CategoryRepositoryInterface
{
    public function getUserCategories(int $userId, array $filters = []): Collection
    {
        $query = Category::where(function ($q) use ($userId) {
            $q->where('user_id', $userId)
                ->orWhereNull('user_id'); // Системні категорії
        });

        if (isset($filters['type'])) {
            $query->where('type', $filters['type']);
        }

        if (isset($filters['is_active'])) {
            $query->where('is_active', $filters['is_active']);
        }

        return $query->orderBy('name')->get();
    }

    public function create(array $data): Category
    {
        return Category::create($data);
    }

    public function update(int $id, array $data): Category
    {
        $category = Category::findOrFail($id);
        $category->update($data);

        return $category->fresh();
    }

    public function delete(int $id): bool
    {
        $category = Category::findOrFail($id);

        // Перевірка: не можна видалити системну категорію
        if ($category->isSystem()) {
            throw new \Exception('Cannot delete system category');
        }

        return $category->delete();
    }

    public function find(int $id): ?Category
    {
        return Category::find($id);
    }
}
