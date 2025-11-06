<?php

namespace App\Services\AI;

use App\Models\Knowledge;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class SemanticSearchService
{
    protected $openAIService;
    protected $knowledgeBaseService;

    public function __construct(
        OpenAIService $openAIService,
        KnowledgeBaseService $knowledgeBaseService
    ) {
        $this->openAIService = $openAIService;
        $this->knowledgeBaseService = $knowledgeBaseService;
    }

    public function search(
        string $query,
        int $limit = 10,
        ?int $categoryId = null,
        float $minSimilarity = 0.7
    ): array {
        try {
            $results = $this->knowledgeBaseService->semanticSearch($query, $limit * 2, $categoryId);
            
            $filtered = array_filter($results, fn($item) => $item['similarity'] >= $minSimilarity);
            
            return array_slice($filtered, 0, $limit);
        } catch (\Exception $e) {
            Log::error('Semantic search error: ' . $e->getMessage());
            throw $e;
        }
    }

    public function searchWithFallback(
        string $query,
        int $limit = 10,
        ?int $categoryId = null,
        float $minSimilarity = 0.7
    ): array {
        try {
            $semanticResults = $this->search($query, $limit, $categoryId, $minSimilarity);
            
            if (count($semanticResults) >= $limit) {
                return $semanticResults;
            }
            
            $keywordResults = $this->keywordSearch($query, $limit - count($semanticResults), $categoryId);
            
            $existingIds = array_map(fn($item) => $item['knowledge']->id, $semanticResults);
            $keywordResults = array_filter(
                $keywordResults,
                fn($item) => !in_array($item['knowledge']->id, $existingIds)
            );
            
            return array_merge($semanticResults, array_values($keywordResults));
        } catch (\Exception $e) {
            Log::error('Semantic search with fallback error: ' . $e->getMessage());
            return $this->keywordSearch($query, $limit, $categoryId);
        }
    }

    public function keywordSearch(string $query, int $limit = 10, ?int $categoryId = null): array
    {
        $knowledgeQuery = Knowledge::where('show', true)
            ->where(function ($q) use ($query) {
                $q->where('title', 'like', "%{$query}%")
                    ->orWhere('body', 'like', "%{$query}%");
            });
        
        if ($categoryId) {
            $knowledgeQuery->where('category_id', $categoryId);
        }
        
        $results = $knowledgeQuery->limit($limit)->get();
        
        return $results->map(function ($knowledge) {
            return [
                'knowledge' => $knowledge,
                'similarity' => 0.5,
            ];
        })->toArray();
    }

    public function regenerateAllEmbeddings(?int $categoryId = null): array
    {
        $query = Knowledge::where('show', true);
        
        if ($categoryId) {
            $query->where('category_id', $categoryId);
        }
        
        $knowledgeItems = $query->get();
        $stats = [
            'total' => $knowledgeItems->count(),
            'success' => 0,
            'failed' => 0,
        ];
        
        foreach ($knowledgeItems as $knowledge) {
            try {
                $embedding = $this->knowledgeBaseService->generateEmbedding($knowledge);
                
                $knowledge->embedding = $embedding;
                $knowledge->embedding_generated_at = now();
                $knowledge->save();
                
                $stats['success']++;
            } catch (\Exception $e) {
                Log::error("Failed to generate embedding for knowledge {$knowledge->id}: " . $e->getMessage());
                $stats['failed']++;
            }
        }
        
        return $stats;
    }

    public function regenerateEmbedding(int $knowledgeId): bool
    {
        try {
            $knowledge = Knowledge::findOrFail($knowledgeId);
            
            $embedding = $this->knowledgeBaseService->generateEmbedding($knowledge);
            
            $knowledge->embedding = $embedding;
            $knowledge->embedding_generated_at = now();
            $knowledge->save();
            
            return true;
        } catch (\Exception $e) {
            Log::error("Failed to regenerate embedding for knowledge {$knowledgeId}: " . $e->getMessage());
            return false;
        }
    }
}
