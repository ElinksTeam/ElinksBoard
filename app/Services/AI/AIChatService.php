<?php

namespace App\Services\AI;

use App\Models\Knowledge;
use App\Models\ChatConversation;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class AIChatService
{
    protected $openAIService;
    protected $knowledgeBaseService;
    protected $systemPrompt;

    public function __construct(
        OpenAIService $openAIService,
        KnowledgeBaseService $knowledgeBaseService
    ) {
        $this->openAIService = $openAIService;
        $this->knowledgeBaseService = $knowledgeBaseService;
        $this->systemPrompt = Config::get('services.openai.system_prompt', $this->getDefaultSystemPrompt());
    }

    public function createSession(?int $userId = null, ?int $categoryId = null): string
    {
        $sessionId = Str::uuid()->toString();
        
        ChatConversation::create([
            'user_id' => $userId,
            'session_id' => $sessionId,
            'category_id' => $categoryId,
            'messages' => [],
        ]);
        
        return $sessionId;
    }

    public function getSession(string $sessionId): ?ChatConversation
    {
        return ChatConversation::where('session_id', $sessionId)->first();
    }

    public function chat(
        string $userMessage,
        ?string $sessionId = null,
        ?int $categoryId = null,
        array $conversationHistory = []
    ): array {
        try {
            $conversation = null;
            if ($sessionId) {
                $conversation = $this->getSession($sessionId);
                if ($conversation) {
                    $conversationHistory = $conversation->getConversationHistory();
                    $categoryId = $categoryId ?? $conversation->category_id;
                }
            }
            
            $relevantKnowledge = $this->knowledgeBaseService->semanticSearch(
                $userMessage,
                3,
                $categoryId
            );
            
            $context = $this->buildContext($relevantKnowledge);
            
            $messages = $this->buildMessages($userMessage, $context, $conversationHistory);
            
            $response = $this->openAIService->chat($messages);
            
            if ($conversation) {
                $conversation->addMessage('user', $userMessage);
                $conversation->addMessage('assistant', $response);
            }
            
            return [
                'response' => $response,
                'session_id' => $sessionId,
                'sources' => array_map(fn($item) => [
                    'id' => $item['knowledge']->id,
                    'title' => $item['knowledge']->title,
                    'similarity' => $item['similarity'],
                ], $relevantKnowledge),
            ];
        } catch (\Exception $e) {
            Log::error('AI chat error: ' . $e->getMessage());
            throw $e;
        }
    }

    public function streamChat(
        string $userMessage,
        callable $callback,
        ?string $sessionId = null,
        ?int $categoryId = null,
        array $conversationHistory = []
    ): array {
        try {
            $conversation = null;
            $fullResponse = '';
            
            if ($sessionId) {
                $conversation = $this->getSession($sessionId);
                if ($conversation) {
                    $conversationHistory = $conversation->getConversationHistory();
                    $categoryId = $categoryId ?? $conversation->category_id;
                }
            }
            
            $relevantKnowledge = $this->knowledgeBaseService->semanticSearch(
                $userMessage,
                3,
                $categoryId
            );
            
            $context = $this->buildContext($relevantKnowledge);
            
            $messages = $this->buildMessages($userMessage, $context, $conversationHistory);
            
            $this->openAIService->streamChat($messages, function ($chunk) use ($callback, &$fullResponse) {
                $fullResponse .= $chunk;
                $callback($chunk);
            });
            
            if ($conversation) {
                $conversation->addMessage('user', $userMessage);
                $conversation->addMessage('assistant', $fullResponse);
            }
            
            return [
                'session_id' => $sessionId,
                'sources' => array_map(fn($item) => [
                    'id' => $item['knowledge']->id,
                    'title' => $item['knowledge']->title,
                    'similarity' => $item['similarity'],
                ], $relevantKnowledge),
            ];
        } catch (\Exception $e) {
            Log::error('AI stream chat error: ' . $e->getMessage());
            throw $e;
        }
    }

    protected function buildContext(array $relevantKnowledge): string
    {
        if (empty($relevantKnowledge)) {
            return '';
        }
        
        $contextParts = ["以下是相关的知识库内容：\n"];
        
        foreach ($relevantKnowledge as $index => $item) {
            $knowledge = $item['knowledge'];
            $contextParts[] = sprintf(
                "[文档 %d] %s\n%s\n",
                $index + 1,
                $knowledge->title,
                strip_tags($knowledge->body)
            );
        }
        
        return implode("\n", $contextParts);
    }

    protected function buildMessages(
        string $userMessage,
        string $context,
        array $conversationHistory
    ): array {
        $messages = [
            ['role' => 'system', 'content' => $this->systemPrompt],
        ];
        
        foreach ($conversationHistory as $message) {
            $messages[] = $message;
        }
        
        if ($context) {
            $messages[] = [
                'role' => 'system',
                'content' => $context,
            ];
        }
        
        $messages[] = [
            'role' => 'user',
            'content' => $userMessage,
        ];
        
        return $messages;
    }

    protected function getDefaultSystemPrompt(): string
    {
        return <<<PROMPT
你是一个专业的客服助手，负责回答用户关于产品和服务的问题。

请遵循以下规则：
1. 基于提供的知识库内容回答问题
2. 如果知识库中没有相关信息，请诚实地告知用户
3. 保持回答简洁、准确、友好
4. 如果问题不清楚，请要求用户提供更多信息
5. 在回答中引用相关的知识库文档编号

请用中文回答问题。
PROMPT;
    }
}
