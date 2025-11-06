<?php

namespace App\Console\Commands\AI;

use App\Services\AI\OpenAIService;
use App\Services\AI\SemanticSearchService;
use App\Services\AI\AIChatService;
use Illuminate\Console\Command;

class TestAIFeatures extends Command
{
    protected $signature = 'ai:test 
                            {--search= : Test semantic search with query}
                            {--chat= : Test AI chat with message}
                            {--embedding : Test embedding generation}';

    protected $description = 'Test AI features (search, chat, embeddings)';

    protected $openAIService;
    protected $semanticSearchService;
    protected $aiChatService;

    public function __construct(
        OpenAIService $openAIService,
        SemanticSearchService $semanticSearchService,
        AIChatService $aiChatService
    ) {
        parent::__construct();
        $this->openAIService = $openAIService;
        $this->semanticSearchService = $semanticSearchService;
        $this->aiChatService = $aiChatService;
    }

    public function handle()
    {
        if ($this->option('embedding')) {
            return $this->testEmbedding();
        }

        if ($query = $this->option('search')) {
            return $this->testSearch($query);
        }

        if ($message = $this->option('chat')) {
            return $this->testChat($message);
        }

        $this->error('Please specify a test option: --search, --chat, or --embedding');
        return Command::FAILURE;
    }

    protected function testEmbedding()
    {
        $this->info('Testing embedding generation...');
        
        try {
            $text = 'This is a test text for embedding generation.';
            $this->line("Text: {$text}");
            
            $embedding = $this->openAIService->createEmbedding($text);
            
            $this->info('✅ Embedding generated successfully');
            $this->line('Dimensions: ' . count($embedding));
            $this->line('First 5 values: ' . implode(', ', array_slice($embedding, 0, 5)));
            
            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error('❌ Embedding test failed: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }

    protected function testSearch(string $query)
    {
        $this->info("Testing semantic search with query: {$query}");
        
        try {
            $results = $this->semanticSearchService->search($query, 5, null, 0.0);
            
            if (empty($results)) {
                $this->warn('No results found');
                return Command::SUCCESS;
            }
            
            $this->info("✅ Found {count($results)} results:");
            $this->newLine();
            
            $tableData = [];
            foreach ($results as $result) {
                $tableData[] = [
                    $result['knowledge']->id,
                    $result['knowledge']->title,
                    round($result['similarity'], 4),
                ];
            }
            
            $this->table(['ID', 'Title', 'Similarity'], $tableData);
            
            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error('❌ Search test failed: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }

    protected function testChat(string $message)
    {
        $this->info("Testing AI chat with message: {$message}");
        
        try {
            $result = $this->aiChatService->chat($message);
            
            $this->info('✅ Chat response received:');
            $this->newLine();
            $this->line($result['response']);
            $this->newLine();
            
            if (!empty($result['sources'])) {
                $this->info('Sources:');
                foreach ($result['sources'] as $source) {
                    $this->line("- [{$source['id']}] {$source['title']} (similarity: {$source['similarity']})");
                }
            }
            
            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error('❌ Chat test failed: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
