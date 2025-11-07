<?php

namespace App\Services\AI;

use App\Exceptions\MissingAIApiKeyException;
use OpenAI;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;

class OpenAIService implements AIProviderInterface
{
    protected $client;
    protected $model;
    protected $embeddingModel;

    public function __construct()
    {
        $this->model = Config::get('services.openai.model', 'gpt-4o-mini');
        $this->embeddingModel = Config::get('services.openai.embedding_model', 'text-embedding-3-small');
    }

    protected function getClient()
    {
        if ($this->client) {
            return $this->client;
        }

        $apiKey = Config::get('services.openai.api_key');
        $baseUrl = Config::get('services.openai.base_url');
        
        if (!$apiKey) {
            throw new MissingAIApiKeyException('OpenAI');
        }

        $config = ['api_key' => $apiKey];
        if ($baseUrl) {
            $config['base_uri'] = $baseUrl;
        }

        $this->client = OpenAI::client($apiKey);
        return $this->client;
    }

    public function chat(array $messages, ?string $model = null): string
    {
        try {
            $client = $this->getClient();
            $response = $client->chat()->create([
                'model' => $model ?? $this->model,
                'messages' => $messages,
            ]);

            return $response->choices[0]->message->content;
        } catch (\Exception $e) {
            Log::error('OpenAI chat error: ' . $e->getMessage());
            throw $e;
        }
    }

    public function streamChat(array $messages, callable $callback, ?string $model = null): void
    {
        try {
            $client = $this->getClient();
            $stream = $client->chat()->createStreamed([
                'model' => $model ?? $this->model,
                'messages' => $messages,
            ]);

            foreach ($stream as $response) {
                if (isset($response->choices[0]->delta->content)) {
                    $callback($response->choices[0]->delta->content);
                }
            }
        } catch (\Exception $e) {
            Log::error('OpenAI stream chat error: ' . $e->getMessage());
            throw $e;
        }
    }

    public function createEmbedding(string $text, ?string $model = null): array
    {
        try {
            $client = $this->getClient();
            $response = $client->embeddings()->create([
                'model' => $model ?? $this->embeddingModel,
                'input' => $text,
            ]);

            return $response->embeddings[0]->embedding;
        } catch (\Exception $e) {
            Log::error('OpenAI embedding error: ' . $e->getMessage());
            throw $e;
        }
    }

    public function createBatchEmbeddings(array $texts, ?string $model = null): array
    {
        try {
            $client = $this->getClient();
            $response = $client->embeddings()->create([
                'model' => $model ?? $this->embeddingModel,
                'input' => $texts,
            ]);

            return array_map(fn($embedding) => $embedding->embedding, $response->embeddings);
        } catch (\Exception $e) {
            Log::error('OpenAI batch embedding error: ' . $e->getMessage());
            throw $e;
        }
    }
}
