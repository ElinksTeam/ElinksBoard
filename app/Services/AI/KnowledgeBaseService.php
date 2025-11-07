<?php

namespace App\Services\AI;

use App\Models\Knowledge;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class KnowledgeBaseService
{
    protected $aiProvider;
    protected $cachePrefix = 'kb_embedding:';
    protected $cacheTTL = 86400;

    public function __construct(?string $provider = null)
    {
        $this->aiProvider = AIProviderFactory::make($provider);
    }

    public function generateEmbedding(Knowledge $knowledge): array
    {
        $text = $this->prepareTextForEmbedding($knowledge);
        $embedding = $this->aiProvider->createEmbedding($text);
        
        Cache::put($this->cachePrefix . $knowledge->id, $embedding, $this->cacheTTL);
        
        return $embedding;
    }

    public function batchGenerateEmbeddings(array $knowledgeIds): array
    {
        $knowledgeItems = Knowledge::whereIn('id', $knowledgeIds)->get();
        $texts = $knowledgeItems->map(fn($k) => $this->prepareTextForEmbedding($k))->toArray();
        
        $embeddings = $this->aiProvider->createBatchEmbeddings($texts);
        
        foreach ($knowledgeItems as $index => $knowledge) {
            Cache::put($this->cachePrefix . $knowledge->id, $embeddings[$index], $this->cacheTTL);
        }
        
        return $embeddings;
    }

    public function getEmbedding(Knowledge $knowledge): ?array
    {
        $cached = Cache::get($this->cachePrefix . $knowledge->id);
        if ($cached) {
            return $cached;
        }
        
        return $this->generateEmbedding($knowledge);
    }

    public function semanticSearch(string $query, int $limit = 5, ?int $categoryId = null): array
    {
        try {
            $queryEmbedding = $this->aiProvider->createEmbedding($query);
            
            $knowledgeQuery = Knowledge::where('show', true);
            
            if ($categoryId) {
                $knowledgeQuery->where('category_id', $categoryId);
            }
            
            $knowledgeItems = $knowledgeQuery->get();
            
            $results = [];
            foreach ($knowledgeItems as $knowledge) {
                $embedding = $this->getEmbedding($knowledge);
                if ($embedding) {
                    $similarity = $this->cosineSimilarity($queryEmbedding, $embedding);
                    $results[] = [
                        'knowledge' => $knowledge,
                        'similarity' => $similarity,
                    ];
                }
            }
            
            usort($results, fn($a, $b) => $b['similarity'] <=> $a['similarity']);
            
            return array_slice($results, 0, $limit);
        } catch (\Exception $e) {
            Log::error('Semantic search error: ' . $e->getMessage());
            throw $e;
        }
    }

    public function clearEmbeddingCache(?int $knowledgeId = null): void
    {
        if ($knowledgeId) {
            Cache::forget($this->cachePrefix . $knowledgeId);
        } else {
            $keys = Cache::get('kb_embedding_keys', []);
            foreach ($keys as $key) {
                Cache::forget($key);
            }
        }
    }

    protected function prepareTextForEmbedding(Knowledge $knowledge): string
    {
        $parts = [];
        
        if ($knowledge->title) {
            $parts[] = $knowledge->title;
        }
        
        if ($knowledge->body) {
            $parts[] = strip_tags($knowledge->body);
        }
        
        return implode("\n\n", $parts);
    }

    protected function cosineSimilarity(array $a, array $b): float
    {
        $dotProduct = 0;
        $magnitudeA = 0;
        $magnitudeB = 0;
        
        $count = min(count($a), count($b));
        
        for ($i = 0; $i < $count; $i++) {
            $dotProduct += $a[$i] * $b[$i];
            $magnitudeA += $a[$i] * $a[$i];
            $magnitudeB += $b[$i] * $b[$i];
        }
        
        $magnitudeA = sqrt($magnitudeA);
        $magnitudeB = sqrt($magnitudeB);
        
        if ($magnitudeA == 0 || $magnitudeB == 0) {
            return 0;
        }
        
        return $dotProduct / ($magnitudeA * $magnitudeB);
    }
}
