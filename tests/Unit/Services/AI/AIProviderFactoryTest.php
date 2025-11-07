<?php

namespace Tests\Unit\Services\AI;

use App\Services\AI\AIProviderFactory;
use App\Services\AI\AIProviderInterface;
use App\Services\AI\GeminiService;
use App\Services\AI\OpenAIService;
use Illuminate\Support\Facades\Config;
use Tests\TestCase;

class AIProviderFactoryTest extends TestCase
{
    /**
     * Test that factory can create OpenAI provider
     */
    public function test_can_create_openai_provider(): void
    {
        $provider = AIProviderFactory::make('openai');
        
        $this->assertInstanceOf(AIProviderInterface::class, $provider);
        $this->assertInstanceOf(OpenAIService::class, $provider);
    }

    /**
     * Test that factory can create Gemini provider
     */
    public function test_can_create_gemini_provider(): void
    {
        $provider = AIProviderFactory::make('gemini');
        
        $this->assertInstanceOf(AIProviderInterface::class, $provider);
        $this->assertInstanceOf(GeminiService::class, $provider);
    }

    /**
     * Test that factory uses default provider from config
     */
    public function test_uses_default_provider_from_config(): void
    {
        Config::set('services.ai.default_provider', 'openai');
        $provider = AIProviderFactory::default();
        
        $this->assertInstanceOf(OpenAIService::class, $provider);
    }

    /**
     * Test that factory throws exception for unsupported provider
     */
    public function test_throws_exception_for_unsupported_provider(): void
    {
        $this->expectException(\App\Exceptions\UnsupportedAIProviderException::class);
        $this->expectExceptionMessage('Unsupported AI provider: invalid');
        
        AIProviderFactory::make('invalid');
    }

    /**
     * Test that factory returns correct provider when specified
     */
    public function test_make_returns_correct_provider_when_specified(): void
    {
        // Even if default is openai, should return gemini when specified
        Config::set('services.ai.default_provider', 'openai');
        $provider = AIProviderFactory::make('gemini');
        
        $this->assertInstanceOf(GeminiService::class, $provider);
    }
}
