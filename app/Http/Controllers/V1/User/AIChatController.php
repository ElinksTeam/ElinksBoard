<?php

namespace App\Http\Controllers\V1\User;

use App\Http\Controllers\Controller;
use App\Services\AI\AIChatService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AIChatController extends Controller
{
    protected $aiChatService;

    public function __construct(AIChatService $aiChatService)
    {
        $this->aiChatService = $aiChatService;
    }

    public function createSession(Request $request)
    {
        $validated = $request->validate([
            'category_id' => 'nullable|integer|min:1',
        ]);

        try {
            $userId = $request->user() ? $request->user()->id : null;
            $sessionId = $this->aiChatService->createSession(
                $userId,
                $validated['category_id'] ?? null
            );

            return $this->success([
                'session_id' => $sessionId,
            ]);
        } catch (\Exception $e) {
            Log::error('Create chat session error: ' . $e->getMessage());
            return $this->fail([500, __('Failed to create chat session.')]);
        }
    }

    public function chat(Request $request)
    {
        $validated = $request->validate([
            'message' => 'required|string|max:2000',
            'session_id' => 'nullable|string|uuid',
            'category_id' => 'nullable|integer|min:1',
        ]);

        try {
            $result = $this->aiChatService->chat(
                $validated['message'],
                $validated['session_id'] ?? null,
                $validated['category_id'] ?? null
            );

            return $this->success([
                'response' => $result['response'],
                'session_id' => $result['session_id'],
                'sources' => $result['sources'],
            ]);
        } catch (\Exception $e) {
            Log::error('AI chat error: ' . $e->getMessage());
            return $this->fail([500, __('Chat failed. Please try again later.')]);
        }
    }

    public function streamChat(Request $request)
    {
        $validated = $request->validate([
            'message' => 'required|string|max:2000',
            'session_id' => 'nullable|string|uuid',
            'category_id' => 'nullable|integer|min:1',
        ]);

        try {
            return new StreamedResponse(function () use ($validated) {
                $sources = [];
                
                $result = $this->aiChatService->streamChat(
                    $validated['message'],
                    function ($chunk) {
                        echo "data: " . json_encode(['type' => 'content', 'data' => $chunk]) . "\n\n";
                        if (ob_get_level() > 0) {
                            ob_flush();
                        }
                        flush();
                    },
                    $validated['session_id'] ?? null,
                    $validated['category_id'] ?? null
                );
                
                echo "data: " . json_encode([
                    'type' => 'sources',
                    'data' => $result['sources'],
                    'session_id' => $result['session_id'],
                ]) . "\n\n";
                
                echo "data: " . json_encode(['type' => 'done']) . "\n\n";
                
                if (ob_get_level() > 0) {
                    ob_flush();
                }
                flush();
            }, 200, [
                'Content-Type' => 'text/event-stream',
                'Cache-Control' => 'no-cache',
                'X-Accel-Buffering' => 'no',
            ]);
        } catch (\Exception $e) {
            Log::error('AI stream chat error: ' . $e->getMessage());
            return $this->fail([500, __('Chat failed. Please try again later.')]);
        }
    }

    public function getSession(Request $request, string $sessionId)
    {
        try {
            $session = $this->aiChatService->getSession($sessionId);
            
            if (!$session) {
                return $this->fail([404, __('Session not found.')]);
            }
            
            if ($request->user() && $session->user_id !== $request->user()->id) {
                return $this->fail([403, __('Access denied.')]);
            }

            return $this->success([
                'session_id' => $session->session_id,
                'category_id' => $session->category_id,
                'messages' => $session->messages,
                'created_at' => $session->created_at,
                'updated_at' => $session->updated_at,
            ]);
        } catch (\Exception $e) {
            Log::error('Get chat session error: ' . $e->getMessage());
            return $this->fail([500, __('Failed to retrieve session.')]);
        }
    }
}
