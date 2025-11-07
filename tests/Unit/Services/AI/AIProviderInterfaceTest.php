<?php

namespace Tests\Unit\Services\AI;

use App\Services\AI\AIProviderInterface;
use App\Services\AI\GeminiService;
use App\Services\AI\OpenAIService;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

class AIProviderInterfaceTest extends TestCase
{
    /**
     * Test that OpenAIService implements AIProviderInterface
     */
    public function test_openai_service_implements_interface(): void
    {
        $reflection = new ReflectionClass(OpenAIService::class);
        $this->assertTrue($reflection->implementsInterface(AIProviderInterface::class));
    }

    /**
     * Test that GeminiService implements AIProviderInterface
     */
    public function test_gemini_service_implements_interface(): void
    {
        $reflection = new ReflectionClass(GeminiService::class);
        $this->assertTrue($reflection->implementsInterface(AIProviderInterface::class));
    }

    /**
     * Test that OpenAIService has all required methods
     */
    public function test_openai_service_has_required_methods(): void
    {
        $reflection = new ReflectionClass(OpenAIService::class);
        
        $this->assertTrue($reflection->hasMethod('chat'));
        $this->assertTrue($reflection->hasMethod('streamChat'));
        $this->assertTrue($reflection->hasMethod('createEmbedding'));
        $this->assertTrue($reflection->hasMethod('createBatchEmbeddings'));
    }

    /**
     * Test that GeminiService has all required methods
     */
    public function test_gemini_service_has_required_methods(): void
    {
        $reflection = new ReflectionClass(GeminiService::class);
        
        $this->assertTrue($reflection->hasMethod('chat'));
        $this->assertTrue($reflection->hasMethod('streamChat'));
        $this->assertTrue($reflection->hasMethod('createEmbedding'));
        $this->assertTrue($reflection->hasMethod('createBatchEmbeddings'));
    }

    /**
     * Test that chat method has correct signature
     */
    public function test_chat_method_signature(): void
    {
        $interface = new ReflectionClass(AIProviderInterface::class);
        $method = $interface->getMethod('chat');
        
        $this->assertCount(2, $method->getParameters());
        $this->assertEquals('messages', $method->getParameters()[0]->getName());
        $this->assertEquals('model', $method->getParameters()[1]->getName());
    }

    /**
     * Test that streamChat method has correct signature
     */
    public function test_stream_chat_method_signature(): void
    {
        $interface = new ReflectionClass(AIProviderInterface::class);
        $method = $interface->getMethod('streamChat');
        
        $this->assertCount(3, $method->getParameters());
        $this->assertEquals('messages', $method->getParameters()[0]->getName());
        $this->assertEquals('callback', $method->getParameters()[1]->getName());
        $this->assertEquals('model', $method->getParameters()[2]->getName());
    }

    /**
     * Test that createEmbedding method has correct signature
     */
    public function test_create_embedding_method_signature(): void
    {
        $interface = new ReflectionClass(AIProviderInterface::class);
        $method = $interface->getMethod('createEmbedding');
        
        $this->assertCount(2, $method->getParameters());
        $this->assertEquals('text', $method->getParameters()[0]->getName());
        $this->assertEquals('model', $method->getParameters()[1]->getName());
    }

    /**
     * Test that createBatchEmbeddings method has correct signature
     */
    public function test_create_batch_embeddings_method_signature(): void
    {
        $interface = new ReflectionClass(AIProviderInterface::class);
        $method = $interface->getMethod('createBatchEmbeddings');
        
        $this->assertCount(2, $method->getParameters());
        $this->assertEquals('texts', $method->getParameters()[0]->getName());
        $this->assertEquals('model', $method->getParameters()[1]->getName());
    }
}
