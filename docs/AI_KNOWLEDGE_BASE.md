# AI 智能知識庫設計文檔

## 概述

將 ElinksBoard 知識庫升級為 AI 驅動的智能系統，提供：
- 🤖 AI 智能問答
- 🔍 語義搜尋
- 💡 智能推薦
- 📊 使用分析
- 🌐 多語言支援

## 架構設計

### 1. 核心組件

```
┌─────────────────────────────────────────────────┐
│                   前端介面                        │
│  ┌──────────┐  ┌──────────┐  ┌──────────┐      │
│  │ 搜尋框   │  │ AI 問答  │  │ 推薦卡片 │      │
│  └──────────┘  └──────────┘  └──────────┘      │
└─────────────────────────────────────────────────┘
                      ↓
┌─────────────────────────────────────────────────┐
│              AI 服務層 (Laravel)                 │
│  ┌──────────┐  ┌──────────┐  ┌──────────┐      │
│  │語義搜尋  │  │ AI 問答  │  │智能推薦  │      │
│  └──────────┘  └──────────┘  └──────────┘      │
└─────────────────────────────────────────────────┘
                      ↓
┌─────────────────────────────────────────────────┐
│              AI 引擎層                           │
│  ┌──────────┐  ┌──────────┐  ┌──────────┐      │
│  │ OpenAI   │  │ Embedding│  │ Vector DB│      │
│  │   API    │  │  Service │  │  (Redis) │      │
│  └──────────┘  └──────────┘  └──────────┘      │
└─────────────────────────────────────────────────┘
                      ↓
┌─────────────────────────────────────────────────┐
│              資料層                              │
│  ┌──────────┐  ┌──────────┐  ┌──────────┐      │
│  │ MySQL    │  │  Redis   │  │ 搜尋索引 │      │
│  └──────────┘  └──────────┘  └──────────┘      │
└─────────────────────────────────────────────────┘
```

### 2. 資料庫擴展

```sql
-- 添加 AI 相關欄位
ALTER TABLE v2_knowledge ADD COLUMN embedding TEXT COMMENT 'AI 向量嵌入';
ALTER TABLE v2_knowledge ADD COLUMN embedding_model VARCHAR(50) COMMENT '嵌入模型';
ALTER TABLE v2_knowledge ADD COLUMN embedding_updated_at INT COMMENT '嵌入更新時間';
ALTER TABLE v2_knowledge ADD COLUMN view_count INT DEFAULT 0 COMMENT '查看次數';
ALTER TABLE v2_knowledge ADD COLUMN helpful_count INT DEFAULT 0 COMMENT '有幫助次數';
ALTER TABLE v2_knowledge ADD COLUMN unhelpful_count INT DEFAULT 0 COMMENT '無幫助次數';
ALTER TABLE v2_knowledge ADD COLUMN tags JSON COMMENT '標籤';
ALTER TABLE v2_knowledge ADD COLUMN related_ids JSON COMMENT '相關文章 ID';

-- 創建搜尋日誌表
CREATE TABLE v2_knowledge_search_log (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT,
    query TEXT NOT NULL COMMENT '搜尋查詢',
    query_type ENUM('keyword', 'semantic', 'ai_chat') COMMENT '查詢類型',
    results_count INT COMMENT '結果數量',
    clicked_id INT COMMENT '點擊的文章 ID',
    is_helpful BOOLEAN COMMENT '是否有幫助',
    created_at INT COMMENT '創建時間',
    INDEX idx_user_id (user_id),
    INDEX idx_created_at (created_at)
) COMMENT='知識庫搜尋日誌';

-- 創建 AI 對話記錄表
CREATE TABLE v2_knowledge_ai_chat (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT,
    session_id VARCHAR(64) COMMENT '會話 ID',
    question TEXT NOT NULL COMMENT '使用者問題',
    answer TEXT NOT NULL COMMENT 'AI 回答',
    context JSON COMMENT '上下文（引用的文章）',
    model VARCHAR(50) COMMENT '使用的模型',
    tokens_used INT COMMENT '使用的 token 數',
    is_helpful BOOLEAN COMMENT '是否有幫助',
    created_at INT COMMENT '創建時間',
    INDEX idx_user_id (user_id),
    INDEX idx_session_id (session_id),
    INDEX idx_created_at (created_at)
) COMMENT='AI 對話記錄';
```

## 功能實現

### 1. 語義搜尋

**原理：** 使用 OpenAI Embeddings 將文章和查詢轉換為向量，計算相似度

**流程：**
```
使用者輸入查詢
    ↓
生成查詢的 Embedding
    ↓
在向量資料庫中搜尋相似文章
    ↓
返回最相關的結果
```

**實現：**
```php
class SemanticSearchService
{
    public function search(string $query, int $limit = 10): array
    {
        // 1. 生成查詢的 embedding
        $queryEmbedding = $this->generateEmbedding($query);
        
        // 2. 從 Redis 獲取所有文章 embeddings
        $articles = $this->getAllArticleEmbeddings();
        
        // 3. 計算相似度
        $similarities = [];
        foreach ($articles as $article) {
            $similarity = $this->cosineSimilarity(
                $queryEmbedding, 
                $article['embedding']
            );
            $similarities[] = [
                'id' => $article['id'],
                'similarity' => $similarity
            ];
        }
        
        // 4. 排序並返回 top N
        usort($similarities, fn($a, $b) => $b['similarity'] <=> $a['similarity']);
        return array_slice($similarities, 0, $limit);
    }
}
```

### 2. AI 智能問答

**原理：** 使用 RAG (Retrieval-Augmented Generation) 模式

**流程：**
```
使用者提問
    ↓
語義搜尋相關文章
    ↓
將文章作為上下文
    ↓
調用 OpenAI API 生成回答
    ↓
返回答案 + 引用來源
```

**實現：**
```php
class AIKnowledgeService
{
    public function chat(string $question, ?string $sessionId = null): array
    {
        // 1. 搜尋相關文章
        $relevantArticles = $this->semanticSearch->search($question, 3);
        
        // 2. 構建上下文
        $context = $this->buildContext($relevantArticles);
        
        // 3. 構建 prompt
        $prompt = $this->buildPrompt($question, $context);
        
        // 4. 調用 OpenAI
        $response = $this->openai->chat([
            'model' => 'gpt-4-turbo-preview',
            'messages' => [
                ['role' => 'system', 'content' => $this->getSystemPrompt()],
                ['role' => 'user', 'content' => $prompt]
            ],
            'temperature' => 0.7,
            'max_tokens' => 1000
        ]);
        
        // 5. 記錄對話
        $this->logChat($question, $response, $relevantArticles);
        
        return [
            'answer' => $response['choices'][0]['message']['content'],
            'sources' => $relevantArticles,
            'session_id' => $sessionId ?? Str::random(32)
        ];
    }
    
    private function getSystemPrompt(): string
    {
        return "你是 ElinksBoard 的智能助手。根據提供的知識庫內容回答使用者問題。
        
規則：
1. 只使用提供的知識庫內容回答
2. 如果知識庫中沒有相關資訊，誠實告知
3. 回答要清晰、準確、有幫助
4. 使用繁體中文回答
5. 可以提供步驟說明和範例";
    }
}
```

### 3. 智能推薦

**策略：**
1. **基於內容** - 相似文章推薦
2. **基於行為** - 使用者閱讀歷史
3. **基於熱度** - 熱門文章
4. **基於上下文** - 根據當前問題推薦

**實現：**
```php
class KnowledgeRecommendationService
{
    public function recommend(User $user, ?int $currentArticleId = null): array
    {
        $recommendations = [];
        
        // 1. 基於當前文章的相似推薦
        if ($currentArticleId) {
            $similar = $this->getSimilarArticles($currentArticleId, 3);
            $recommendations = array_merge($recommendations, $similar);
        }
        
        // 2. 基於使用者歷史
        $history = $this->getUserReadingHistory($user->id, 5);
        if ($history) {
            $personalized = $this->getPersonalizedRecommendations($history, 3);
            $recommendations = array_merge($recommendations, $personalized);
        }
        
        // 3. 熱門文章
        $popular = $this->getPopularArticles(3);
        $recommendations = array_merge($recommendations, $popular);
        
        // 4. 去重並限制數量
        $recommendations = $this->deduplicateAndLimit($recommendations, 5);
        
        return $recommendations;
    }
}
```

### 4. 自動標籤生成

**使用 AI 自動為文章生成標籤**

```php
class KnowledgeTagService
{
    public function generateTags(Knowledge $article): array
    {
        $prompt = "分析以下文章並生成 3-5 個相關標籤（繁體中文）：

標題：{$article->title}
內容：{$article->body}

要求：
1. 標籤要簡潔（2-4 個字）
2. 標籤要準確反映文章主題
3. 只返回標籤列表，用逗號分隔";

        $response = $this->openai->chat([
            'model' => 'gpt-3.5-turbo',
            'messages' => [
                ['role' => 'user', 'content' => $prompt]
            ],
            'temperature' => 0.5
        ]);
        
        $tagsString = $response['choices'][0]['message']['content'];
        $tags = array_map('trim', explode(',', $tagsString));
        
        return $tags;
    }
}
```

## API 端點設計

### 1. 語義搜尋

```http
POST /api/v1/knowledge/semantic-search
Content-Type: application/json

{
  "query": "如何設定訂閱連結",
  "limit": 10,
  "language": "zh-TW"
}

Response:
{
  "data": [
    {
      "id": 1,
      "title": "訂閱連結設定指南",
      "category": "使用教學",
      "similarity": 0.92,
      "excerpt": "本文介紹如何設定和使用訂閱連結..."
    }
  ]
}
```

### 2. AI 問答

```http
POST /api/v1/knowledge/ai-chat
Content-Type: application/json

{
  "question": "訂閱連結在哪裡找？",
  "session_id": "abc123" // 可選，用於多輪對話
}

Response:
{
  "data": {
    "answer": "您可以在個人中心的「訂閱」頁面找到訂閱連結。具體步驟：\n1. 登入帳號\n2. 點選右上角頭像\n3. 選擇「訂閱管理」\n4. 複製訂閱連結",
    "sources": [
      {
        "id": 1,
        "title": "訂閱連結設定指南",
        "url": "/knowledge/1"
      }
    ],
    "session_id": "abc123",
    "helpful": null
  }
}
```

### 3. 智能推薦

```http
GET /api/v1/knowledge/recommendations?article_id=1

Response:
{
  "data": [
    {
      "id": 2,
      "title": "客戶端配置教學",
      "reason": "similar_content",
      "similarity": 0.85
    },
    {
      "id": 3,
      "title": "常見問題解答",
      "reason": "popular",
      "view_count": 1250
    }
  ]
}
```

### 4. 反饋

```http
POST /api/v1/knowledge/feedback
Content-Type: application/json

{
  "type": "article" | "chat",
  "id": 1,
  "is_helpful": true,
  "comment": "很有幫助！" // 可選
}

Response:
{
  "data": {
    "message": "感謝您的反饋"
  }
}
```

## 配置

### 環境變數

```env
# OpenAI 配置
OPENAI_API_KEY=sk-...
OPENAI_ORGANIZATION=org-...
OPENAI_EMBEDDING_MODEL=text-embedding-3-small
OPENAI_CHAT_MODEL=gpt-4-turbo-preview

# AI 功能開關
AI_KNOWLEDGE_ENABLED=true
AI_SEMANTIC_SEARCH_ENABLED=true
AI_CHAT_ENABLED=true
AI_RECOMMENDATIONS_ENABLED=true

# 限制
AI_CHAT_RATE_LIMIT=10 # 每小時
AI_CHAT_MAX_TOKENS=1000
AI_EMBEDDING_BATCH_SIZE=100
```

### 配置文件

```php
// config/ai.php
return [
    'openai' => [
        'api_key' => env('OPENAI_API_KEY'),
        'organization' => env('OPENAI_ORGANIZATION'),
        'embedding_model' => env('OPENAI_EMBEDDING_MODEL', 'text-embedding-3-small'),
        'chat_model' => env('OPENAI_CHAT_MODEL', 'gpt-4-turbo-preview'),
    ],
    
    'knowledge' => [
        'enabled' => env('AI_KNOWLEDGE_ENABLED', true),
        'semantic_search' => env('AI_SEMANTIC_SEARCH_ENABLED', true),
        'chat' => env('AI_CHAT_ENABLED', true),
        'recommendations' => env('AI_RECOMMENDATIONS_ENABLED', true),
        
        'rate_limits' => [
            'chat_per_hour' => env('AI_CHAT_RATE_LIMIT', 10),
            'search_per_minute' => 30,
        ],
        
        'chat' => [
            'max_tokens' => env('AI_CHAT_MAX_TOKENS', 1000),
            'temperature' => 0.7,
            'context_articles' => 3,
        ],
        
        'embedding' => [
            'batch_size' => env('AI_EMBEDDING_BATCH_SIZE', 100),
            'cache_ttl' => 86400 * 7, // 7 days
        ],
    ],
];
```

## 成本估算

### OpenAI API 成本

**Embeddings (text-embedding-3-small):**
- 價格：$0.02 / 1M tokens
- 假設平均文章 500 tokens
- 100 篇文章 ≈ 50,000 tokens ≈ $0.001

**Chat (gpt-4-turbo-preview):**
- 價格：$0.01 / 1K input tokens, $0.03 / 1K output tokens
- 假設每次對話：1000 input + 500 output tokens
- 成本：$0.01 + $0.015 = $0.025 / 次對話
- 1000 次對話 ≈ $25

**月度估算（中小型網站）:**
- Embeddings 更新：$0.01
- AI 對話（1000 次/月）：$25
- 總計：約 $25-30 / 月

## 實施計劃

### 階段 1：基礎設施（1-2 天）
- [ ] 資料庫遷移
- [ ] OpenAI 服務整合
- [ ] Embedding 生成服務
- [ ] Redis 向量儲存

### 階段 2：核心功能（2-3 天）
- [ ] 語義搜尋實現
- [ ] AI 問答實現
- [ ] 智能推薦實現
- [ ] 反饋系統

### 階段 3：前端整合（1-2 天）
- [ ] 搜尋介面升級
- [ ] AI 聊天介面
- [ ] 推薦卡片
- [ ] 反饋按鈕

### 階段 4：最佳化（1-2 天）
- [ ] 快取策略
- [ ] 效能最佳化
- [ ] 成本控制
- [ ] 監控和日誌

### 階段 5：測試和部署（1 天）
- [ ] 功能測試
- [ ] 效能測試
- [ ] 使用者測試
- [ ] 生產部署

## 監控指標

### 功能指標
- 搜尋準確率
- AI 回答有幫助率
- 推薦點擊率
- 使用者滿意度

### 技術指標
- API 回應時間
- Token 使用量
- 快取命中率
- 錯誤率

### 業務指標
- 知識庫使用率
- 問題解決率
- 客服工單減少率
- API 成本

## 未來擴展

### 1. 多模態支援
- 圖片理解（GPT-4 Vision）
- 語音問答
- 影片內容索引

### 2. 進階功能
- 自動生成文章
- 知識圖譜
- 個性化學習路徑
- 多語言自動翻譯

### 3. 整合
- 與工單系統整合
- 與客服系統整合
- 與使用者行為分析整合

## 參考資源

- [OpenAI API 文件](https://platform.openai.com/docs)
- [Embeddings 指南](https://platform.openai.com/docs/guides/embeddings)
- [RAG 最佳實踐](https://www.pinecone.io/learn/retrieval-augmented-generation/)
- [向量資料庫比較](https://github.com/erikbern/ann-benchmarks)
