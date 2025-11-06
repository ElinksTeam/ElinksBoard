<?php

namespace App\Http\Controllers\V2\Admin;

use App\Http\Controllers\Controller;
use App\Services\AI\SemanticSearchService;
use App\Services\AI\KnowledgeBaseService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AIController extends Controller
{
    protected $semanticSearchService;
    protected $knowledgeBaseService;

    public function __construct(
        SemanticSearchService $semanticSearchService,
        KnowledgeBaseService $knowledgeBaseService
    ) {
        $this->semanticSearchService = $semanticSearchService;
        $this->knowledgeBaseService = $knowledgeBaseService;
    }

    public function regenerateEmbeddings(Request $request)
    {
        $validated = $request->validate([
            'category_id' => 'nullable|integer|min:1',
            'knowledge_id' => 'nullable|integer|min:1',
        ]);

        try {
            if (isset($validated['knowledge_id'])) {
                $success = $this->semanticSearchService->regenerateEmbedding($validated['knowledge_id']);
                
                return $this->success([
                    'success' => $success,
                    'message' => $success ? '向量生成成功' : '向量生成失败',
                ]);
            }
            
            $stats = $this->semanticSearchService->regenerateAllEmbeddings(
                $validated['category_id'] ?? null
            );

            return $this->success([
                'stats' => $stats,
                'message' => "处理完成: 成功 {$stats['success']}, 失败 {$stats['failed']}, 总计 {$stats['total']}",
            ]);
        } catch (\Exception $e) {
            Log::error('Regenerate embeddings error: ' . $e->getMessage());
            return $this->fail([500, '向量生成失败']);
        }
    }

    public function clearEmbeddingCache(Request $request)
    {
        $validated = $request->validate([
            'knowledge_id' => 'nullable|integer|min:1',
        ]);

        try {
            $this->knowledgeBaseService->clearEmbeddingCache(
                $validated['knowledge_id'] ?? null
            );

            return $this->success([
                'message' => '缓存清除成功',
            ]);
        } catch (\Exception $e) {
            Log::error('Clear embedding cache error: ' . $e->getMessage());
            return $this->fail([500, '缓存清除失败']);
        }
    }

    public function testSearch(Request $request)
    {
        $validated = $request->validate([
            'query' => 'required|string|max:500',
            'category_id' => 'nullable|integer|min:1',
            'limit' => 'nullable|integer|min:1|max:20',
        ]);

        try {
            $results = $this->semanticSearchService->search(
                $validated['query'],
                $validated['limit'] ?? 5,
                $validated['category_id'] ?? null,
                0.0
            );

            return $this->success([
                'results' => array_map(function ($item) {
                    return [
                        'id' => $item['knowledge']->id,
                        'title' => $item['knowledge']->title,
                        'category' => $item['knowledge']->category,
                        'similarity' => round($item['similarity'], 4),
                    ];
                }, $results),
            ]);
        } catch (\Exception $e) {
            Log::error('Test search error: ' . $e->getMessage());
            return $this->fail([500, '搜索测试失败']);
        }
    }
}
