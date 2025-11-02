<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class LogController extends Controller
{
    /**
     * Прийом логів з фронтенду
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'level' => 'required|in:error,warning,info,debug',
            'message' => 'required|string|max:1000',
            'context' => 'sometimes|array',
            'url' => 'sometimes|string',
            'timestamp' => 'sometimes|string',
            'userAgent' => 'sometimes|string',
        ]);

        $logData = [
            'message' => $validated['message'],
            'context' => $validated['context'] ?? [],
            'url' => $validated['url'] ?? null,
            'timestamp' => $validated['timestamp'] ?? now(),
            'user_agent' => $validated['userAgent'] ?? $request->userAgent(),
            'user_id' => auth()->id(),
            'user_email' => auth()->user()?->email,
            'ip' => $request->ip(),
            'source' => 'frontend',
        ];

        // Логуємо відповідно до рівня
        $level = $validated['level'];
        Log::channel('errors')->{$level}("Frontend {$level}: ".$validated['message'], $logData);

        return response()->json([
            'success' => true,
            'message' => 'Log received',
        ]);
    }
}
