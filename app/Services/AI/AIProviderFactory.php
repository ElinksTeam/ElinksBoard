<?php

namespace App\Services\AI;

use App\Exceptions\UnsupportedAIProviderException;
use Illuminate\Support\Facades\Config;

class AIProviderFactory
{
    /**
     * Create an AI provider instance based on configuration
     *
     * @param string|null $provider The provider name ('openai' or 'gemini'). If null, uses default from config
     * @return AIProviderInterface
     * @throws UnsupportedAIProviderException
     */
    public static function make(?string $provider = null): AIProviderInterface
    {
        $provider = $provider ?? Config::get('services.ai.default_provider', 'openai');

        return match ($provider) {
            'openai' => app(OpenAIService::class),
            'gemini' => app(GeminiService::class),
            default => throw new UnsupportedAIProviderException($provider)
        };
    }

    /**
     * Get the default AI provider
     *
     * @return AIProviderInterface
     */
    public static function default(): AIProviderInterface
    {
        return self::make();
    }
}
