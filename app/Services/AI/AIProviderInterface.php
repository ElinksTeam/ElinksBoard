<?php

namespace App\Services\AI;

interface AIProviderInterface
{
    /**
     * Send a chat message and get a response
     *
     * @param array $messages Array of messages with 'role' and 'content'
     * @param string|null $model Optional model name to override default
     * @return string The assistant's response
     */
    public function chat(array $messages, ?string $model = null): string;

    /**
     * Stream chat responses
     *
     * @param array $messages Array of messages with 'role' and 'content'
     * @param callable $callback Function to call with each chunk of text
     * @param string|null $model Optional model name to override default
     * @return void
     */
    public function streamChat(array $messages, callable $callback, ?string $model = null): void;

    /**
     * Create an embedding vector from text
     *
     * @param string $text The text to embed
     * @param string|null $model Optional model name to override default
     * @return array The embedding vector
     */
    public function createEmbedding(string $text, ?string $model = null): array;

    /**
     * Create embeddings for multiple texts in a batch
     *
     * @param array $texts Array of text strings to embed
     * @param string|null $model Optional model name to override default
     * @return array Array of embedding vectors
     */
    public function createBatchEmbeddings(array $texts, ?string $model = null): array;
}
