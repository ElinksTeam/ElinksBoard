<?php

namespace App\Console\Commands\AI;

use App\Services\AI\SemanticSearchService;
use Illuminate\Console\Command;

class GenerateEmbeddings extends Command
{
    protected $signature = 'ai:generate-embeddings 
                            {--category= : Filter by category ID}
                            {--knowledge= : Generate for specific knowledge ID}
                            {--force : Force regeneration even if embeddings exist}';

    protected $description = 'Generate embeddings for knowledge base articles';

    protected $semanticSearchService;

    public function __construct(SemanticSearchService $semanticSearchService)
    {
        parent::__construct();
        $this->semanticSearchService = $semanticSearchService;
    }

    public function handle()
    {
        $categoryId = $this->option('category');
        $knowledgeId = $this->option('knowledge');

        if ($knowledgeId) {
            $this->info("Generating embedding for knowledge ID: {$knowledgeId}");
            
            $success = $this->semanticSearchService->regenerateEmbedding($knowledgeId);
            
            if ($success) {
                $this->info('✅ Embedding generated successfully');
                return Command::SUCCESS;
            } else {
                $this->error('❌ Failed to generate embedding');
                return Command::FAILURE;
            }
        }

        $this->info('Generating embeddings for all knowledge articles...');
        if ($categoryId) {
            $this->info("Filtering by category ID: {$categoryId}");
        }

        $stats = $this->semanticSearchService->regenerateAllEmbeddings($categoryId);

        $this->newLine();
        $this->info("Processing complete:");
        $this->table(
            ['Metric', 'Count'],
            [
                ['Total', $stats['total']],
                ['Success', $stats['success']],
                ['Failed', $stats['failed']],
            ]
        );

        return $stats['failed'] > 0 ? Command::FAILURE : Command::SUCCESS;
    }
}
