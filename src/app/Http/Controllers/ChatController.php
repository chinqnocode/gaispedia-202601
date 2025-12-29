<?php

namespace App\Http\Controllers;

use App\Services\GeminiService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ChatController extends Controller
{
    private GeminiService $geminiService;

    public function __construct(GeminiService $geminiService)
    {
        $this->geminiService = $geminiService;
    }

    /**
     * Handle chat message
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function chat(Request $request): JsonResponse
    {
        $request->validate([
            'message' => 'required|string|max:2000',
            'history' => 'nullable|array',
            'history.*.role' => 'required_with:history|string|in:user,model',
            'history.*.content' => 'required_with:history|string',
        ]);

        $message = $request->input('message');
        $history = $request->input('history', []);

        $result = $this->geminiService->simpleChat($message, $history);

        if ($result['success']) {
            return response()->json([
                'success' => true,
                'message' => $result['message'],
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'エラーが発生しました。もう一度お試しください。',
            'error' => $result['error'] ?? 'Unknown error',
        ], 500);
    }
}
