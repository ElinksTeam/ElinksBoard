<?php

namespace App\Services\AI;

use Gemini\Gemini;
use Gemini\Data\Content;
use Gemini\Enums\Role;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;

class GeminiService implements AIProviderInterface
{
    protected $client;
    protected $model;
    protected $embeddingModel;

    public function __construct()
    {
        $this->model = Config::get('services.gemini.model', 'gemini-2.0-flash-exp');
        $this->embeddingModel = Config::get('services.gemini.embedding_model', 'text-embedding-004');
    }

    protected function getClient()
    {
        if ($this->client) {
            return $this->client;
        }

        $apiKey = Config::get('services.gemini.api_key');
        
        if (!$apiKey) {
            throw new \Exception('Gemini API key not configured');
        }

        $this->client = Gemini::client($apiKey);
        return $this->client;
    }

    public function chat(array $messages, ?string $model = null): string
    {
        try {
            $client = $this->getClient();
            $modelName = $model ?? $this->model;
            
            // Convert messages to Gemini format
            $contents = $this->convertMessagesToGeminiFormat($messages);
            
            // Get system prompt if exists
            $systemInstruction = $this->extractSystemPrompt($messages);
            
            $generativeModel = $client->generativeModel(
                model: $modelName,
                systemInstruction: $systemInstruction
            );
            
            $response = $generativeModel->generateContent($contents);

            return $response->text();
        } catch (\Exception $e) {
            Log::error('Gemini chat error: ' . $e->getMessage());
            throw $e;
        }
    }

    public function streamChat(array $messages, callable $callback, ?string $model = null): void
    {
        try {
            $client = $this->getClient();
            $modelName = $model ?? $this->model;
            
            // Convert messages to Gemini format
            $contents = $this->convertMessagesToGeminiFormat($messages);
            
            // Get system prompt if exists
            $systemInstruction = $this->extractSystemPrompt($messages);
            
            $generativeModel = $client->generativeModel(
                model: $modelName,
                systemInstruction: $systemInstruction
            );
            
            $stream = $generativeModel->generateContentStream($contents);

            foreach ($stream as $chunk) {
                $text = $chunk->text();
                if ($text) {
                    $callback($text);
                }
            }
        } catch (\Exception $e) {
            Log::error('Gemini stream chat error: ' . $e->getMessage());
            throw $e;
        }
    }

    public function createEmbedding(string $text, ?string $model = null): array
    {
        try {
            $client = $this->getClient();
            $modelName = $model ?? $this->embeddingModel;
            
            $embeddingModel = $client->embeddingModel(model: $modelName);
            $response = $embeddingModel->embedContent($text);

            return $response->embedding->values;
        } catch (\Exception $e) {
            Log::error('Gemini embedding error: ' . $e->getMessage());
            throw $e;
        }
    }

    public function createBatchEmbeddings(array $texts, ?string $model = null): array
    {
        try {
            $client = $this->getClient();
            $modelName = $model ?? $this->embeddingModel;
            
            $embeddingModel = $client->embeddingModel(model: $modelName);
            $response = $embeddingModel->batchEmbedContents($texts);

            return array_map(fn($embedding) => $embedding->values, $response->embeddings);
        } catch (\Exception $e) {
            Log::error('Gemini batch embedding error: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Convert OpenAI-style messages to Gemini format
     */
    protected function convertMessagesToGeminiFormat(array $messages): array
    {
        $contents = [];
        
        foreach ($messages as $message) {
            // Skip system messages as they are handled separately
            if ($message['role'] === 'system') {
                continue;
            }
            
            // Convert role
            $role = $message['role'] === 'assistant' ? Role::MODEL : Role::USER;
            
            $contents[] = Content::parse([
                'role' => $role->value,
                'parts' => [
                    ['text' => $message['content']]
                ]
            ]);
        }
        
        return $contents;
    }

    /**
     * Extract system prompt from messages
     */
    protected function extractSystemPrompt(array $messages): ?string
    {
        foreach ($messages as $message) {
            if ($message['role'] === 'system') {
                return $message['content'];
            }
        }
        
        return null;
    }
}
