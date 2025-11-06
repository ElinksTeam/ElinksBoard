<?php

namespace App\Http\Controllers\V1\User;

use App\Http\Controllers\Controller;
use App\Services\AI\SemanticSearchService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AISearchController extends Controller
{
    protected $semanticSearchService;

    public function __construct(SemanticSearchService $semanticSearchService)
    {
        $this->semanticSearchService = $semanticSearchService;
    }

    public function search(Request $request)
    {
        $validated = $request->validate([
            'query' => 'required|string|max:500',
            'category_id' => 'nullable|integer|min:1',
            'limit' => 'nullable|integer|min:1|max:20',
            'min_similarity' => 'nullable|numeric|min:0|max:1',
        ]);

        try {
            $results = $this->semanticSearchService->searchWithFallback(
                $validated['query'],
                $validated['limit'] ?? 10,
                $validated['category_id'] ?? null,
                $validated['min_similarity'] ?? 0.7
            );

            return $this->success([
                'results' => array_map(function ($item) {
                    return [
                        'id' => $item['knowledge']->id,
                        'title' => $item['knowledge']->title,
                        'category' => $item['knowledge']->category,
                        'similarity' => round($item['similarity'], 4),
                        'updated_at' => $item['knowledge']->updated_at,
                    ];
                }, $results),
            ]);
        } catch (\Exception $e) {
            Log::error('AI search error: ' . $e->getMessage());
            return $this->fail([500, __('Search failed. Please try again later.')]);
        }
    }

    public function keywordSearch(Request $request)
    {
        $validated = $request->validate([
            'query' => 'required|string|max:500',
            'category_id' => 'nullable|integer|min:1',
            'limit' => 'nullable|integer|min:1|max:20',
        ]);

        try {
            $results = $this->semanticSearchService->keywordSearch(
                $validated['query'],
                $validated['limit'] ?? 10,
                $validated['category_id'] ?? null
            );

            return $this->success([
                'results' => array_map(function ($item) {
                    return [
                        'id' => $item['knowledge']->id,
                        'title' => $item['knowledge']->title,
                        'category' => $item['knowledge']->category,
                        'updated_at' => $item['knowledge']->updated_at,
                    ];
                }, $results),
            ]);
        } catch (\Exception $e) {
            Log::error('Keyword search error: ' . $e->getMessage());
            return $this->fail([500, __('Search failed. Please try again later.')]);
        }
    }
}
