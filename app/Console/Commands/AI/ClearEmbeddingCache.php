<?php

namespace App\Console\Commands\AI;

use App\Services\AI\KnowledgeBaseService;
use Illuminate\Console\Command;

class ClearEmbeddingCache extends Command
{
    protected $signature = 'ai:clear-cache 
                            {--knowledge= : Clear cache for specific knowledge ID}';

    protected $description = 'Clear embedding cache for knowledge base articles';

    protected $knowledgeBaseService;

    public function __construct(KnowledgeBaseService $knowledgeBaseService)
    {
        parent::__construct();
        $this->knowledgeBaseService = $knowledgeBaseService;
    }

    public function handle()
    {
        $knowledgeId = $this->option('knowledge');

        if ($knowledgeId) {
            $this->info("Clearing cache for knowledge ID: {$knowledgeId}");
            $this->knowledgeBaseService->clearEmbeddingCache($knowledgeId);
            $this->info('✅ Cache cleared successfully');
        } else {
            $this->info('Clearing all embedding caches...');
            $this->knowledgeBaseService->clearEmbeddingCache();
            $this->info('✅ All caches cleared successfully');
        }

        return Command::SUCCESS;
    }
}
