# AI 智能知識庫 - 快速入門

## 🚀 快速開始

### 1. 安裝依賴

```bash
# 安裝 OpenAI PHP SDK
composer require openai-php/client

# 如果需要 HTTP 客戶端
composer require guzzlehttp/guzzle
```

### 2. 環境配置

在 `.env` 文件中添加：

```env
# OpenAI API 配置
OPENAI_API_KEY=sk-your-api-key-here
OPENAI_ORGANIZATION=org-your-org-id  # 可選
OPENAI_EMBEDDING_MODEL=text-embedding-3-small
OPENAI_CHAT_MODEL=gpt-4-turbo-preview

# AI 功能開關
AI_KNOWLEDGE_ENABLED=true
AI_SEMANTIC_SEARCH_ENABLED=true
AI_CHAT_ENABLED=true
AI_RECOMMENDATIONS_ENABLED=true

# 速率限制
AI_CHAT_RATE_LIMIT=10  # 每小時
AI_SEARCH_RATE_LIMIT=30  # 每分鐘

# 成本控制
AI_DAILY_BUDGET=10.0  # USD
AI_MONTHLY_BUDGET=100.0  # USD
```

### 3. 執行遷移

```bash
# Docker 環境
docker compose exec web php artisan migrate

# 本地環境
php artisan migrate
```

### 4. 生成文章 Embeddings

```bash
# 為所有文章生成 embeddings
php artisan knowledge:generate-embeddings

# 為特定文章生成
php artisan knowledge:generate-embeddings --id=1

# 批次處理
php artisan knowledge:generate-embeddings --batch=100
```

## 📖 功能說明

### 1. 語義搜尋

**傳統關鍵字搜尋 vs 語義搜尋：**

```
關鍵字搜尋：
查詢："訂閱連結"
結果：只匹配包含"訂閱"和"連結"的文章

語義搜尋：
查詢："訂閱連結"
結果：匹配相關概念的文章
  - "如何獲取訂閱 URL"
  - "訂閱地址設定"
  - "客戶端配置教學"
```

**API 使用：**

```bash
curl -X POST "http://your-domain.com/api/v1/knowledge/semantic-search" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "query": "如何設定訂閱連結",
    "limit": 10
  }'
```

### 2. AI 智能問答

**特點：**
- 理解自然語言問題
- 基於知識庫內容回答
- 提供引用來源
- 支援多輪對話

**API 使用：**

```bash
curl -X POST "http://your-domain.com/api/v1/knowledge/ai-chat" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "question": "訂閱連結在哪裡找？",
    "session_id": "optional-session-id"
  }'
```

**回應範例：**

```json
{
  "data": {
    "answer": "您可以在個人中心的「訂閱」頁面找到訂閱連結...",
    "sources": [
      {
        "id": 1,
        "title": "訂閱連結設定指南",
        "url": "/knowledge/1",
        "relevance": 0.92
      }
    ],
    "session_id": "abc123"
  }
}
```

### 3. 智能推薦

**推薦策略：**
1. 基於內容相似度
2. 基於使用者閱讀歷史
3. 基於文章熱度
4. 基於當前上下文

**API 使用：**

```bash
# 獲取推薦文章
curl -X GET "http://your-domain.com/api/v1/knowledge/recommendations?article_id=1" \
  -H "Authorization: Bearer YOUR_TOKEN"
```

### 4. 反饋系統

**收集使用者反饋以改進 AI：**

```bash
curl -X POST "http://your-domain.com/api/v1/knowledge/feedback" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "type": "chat",
    "id": 1,
    "is_helpful": true,
    "comment": "回答很準確！"
  }'
```

## 🛠️ 管理命令

### Artisan 命令

```bash
# 生成 embeddings
php artisan knowledge:generate-embeddings [--id=ID] [--batch=100]

# 更新過期的 embeddings
php artisan knowledge:update-embeddings [--days=7]

# 生成文章標籤
php artisan knowledge:generate-tags [--id=ID]

# 計算相關文章
php artisan knowledge:calculate-related [--id=ID]

# 清理舊的搜尋日誌
php artisan knowledge:clean-logs [--days=30]

# 查看 AI 使用統計
php artisan knowledge:ai-stats [--period=month]

# 測試 OpenAI 連接
php artisan knowledge:test-openai
```

### 排程任務

在 `app/Console/Kernel.php` 中添加：

```php
protected function schedule(Schedule $schedule)
{
    // 每天凌晨更新 embeddings
    $schedule->command('knowledge:update-embeddings')
        ->daily()
        ->at('02:00');
    
    // 每週計算相關文章
    $schedule->command('knowledge:calculate-related')
        ->weekly()
        ->sundays()
        ->at('03:00');
    
    // 每月清理舊日誌
    $schedule->command('knowledge:clean-logs --days=90')
        ->monthly();
}
```

## 💰 成本管理

### 估算成本

**Embeddings:**
- 模型：text-embedding-3-small
- 價格：$0.02 / 1M tokens
- 平均文章：500 tokens
- 100 篇文章：$0.001

**Chat:**
- 模型：gpt-4-turbo-preview
- 價格：$0.01 / 1K input + $0.03 / 1K output
- 平均對話：1000 input + 500 output tokens
- 成本：$0.025 / 次

**月度預算（參考）：**
- 小型網站（<100 文章，<500 對話/月）：$15-20
- 中型網站（<500 文章，<2000 對話/月）：$50-70
- 大型網站（>1000 文章，>5000 對話/月）：$150-200

### 成本控制

1. **啟用快取**
   ```env
   AI_CACHE_ENABLED=true
   ```

2. **設定預算限制**
   ```env
   AI_DAILY_BUDGET=10.0
   AI_MONTHLY_BUDGET=100.0
   ```

3. **使用更便宜的模型**
   ```env
   # Chat 使用 GPT-3.5 而非 GPT-4
   OPENAI_CHAT_MODEL=gpt-3.5-turbo
   
   # Embedding 使用較小的模型
   OPENAI_EMBEDDING_MODEL=text-embedding-3-small
   ```

4. **限制使用頻率**
   ```env
   AI_CHAT_RATE_LIMIT=5  # 降低每小時限制
   ```

### 監控成本

```bash
# 查看本月使用情況
php artisan knowledge:ai-stats --period=month

# 查看今日使用情況
php artisan knowledge:ai-stats --period=today

# 導出詳細報告
php artisan knowledge:ai-stats --export=csv
```

## 🔧 故障排除

### 問題 1：OpenAI API 連接失敗

**檢查：**
```bash
php artisan knowledge:test-openai
```

**可能原因：**
- API Key 錯誤
- 網路連接問題
- API 配額用盡

**解決：**
1. 驗證 API Key：https://platform.openai.com/api-keys
2. 檢查配額：https://platform.openai.com/usage
3. 檢查網路連接

### 問題 2：Embedding 生成失敗

**檢查日誌：**
```bash
tail -f storage/logs/laravel.log | grep "embedding"
```

**可能原因：**
- 文章內容過長
- API 速率限制
- 記憶體不足

**解決：**
1. 減小批次大小：`--batch=50`
2. 增加延遲時間
3. 分段處理長文章

### 問題 3：搜尋結果不準確

**改進方法：**
1. 重新生成 embeddings
2. 調整相似度閾值
3. 增加訓練資料
4. 使用更好的模型

### 問題 4：成本過高

**優化策略：**
1. 啟用快取
2. 使用較便宜的模型
3. 減少 API 調用頻率
4. 批次處理請求

## 📊 效能最佳化

### 1. 快取策略

```php
// 快取 embeddings
Cache::remember("knowledge:embedding:{$id}", 86400 * 7, function() {
    return $this->generateEmbedding($article);
});

// 快取搜尋結果
Cache::remember("knowledge:search:{$query}", 3600, function() {
    return $this->semanticSearch($query);
});
```

### 2. 批次處理

```php
// 批次生成 embeddings
$articles = Knowledge::whereNull('embedding')->take(100)->get();
foreach ($articles->chunk(10) as $chunk) {
    $this->generateEmbeddingsBatch($chunk);
    sleep(1); // 避免速率限制
}
```

### 3. 非同步處理

```php
// 使用佇列處理 embedding 生成
dispatch(new GenerateEmbeddingJob($article));

// 使用佇列處理 AI 對話
dispatch(new ProcessAIChatJob($question, $userId));
```

## 🔐 安全建議

### 1. API Key 安全

- ✅ 使用環境變數儲存 API Key
- ✅ 不要提交 `.env` 到版本控制
- ✅ 定期輪換 API Key
- ✅ 使用組織級別的 API Key

### 2. 速率限制

```php
// 在 Controller 中添加速率限制
public function chat(Request $request)
{
    $user = $request->user();
    
    // 檢查速率限制
    if (RateLimiter::tooManyAttempts("ai-chat:{$user->id}", 10)) {
        return response()->json([
            'message' => '請求過於頻繁，請稍後再試'
        ], 429);
    }
    
    RateLimiter::hit("ai-chat:{$user->id}", 3600);
    
    // 處理請求...
}
```

### 3. 內容過濾

```php
// 過濾敏感內容
public function chat(Request $request)
{
    $question = $request->input('question');
    
    // 檢查是否包含敏感詞
    if ($this->containsSensitiveContent($question)) {
        return response()->json([
            'message' => '問題包含不當內容'
        ], 400);
    }
    
    // 處理請求...
}
```

## 📚 相關資源

- [完整設計文檔](docs/AI_KNOWLEDGE_BASE.md)
- [OpenAI API 文件](https://platform.openai.com/docs)
- [Embeddings 指南](https://platform.openai.com/docs/guides/embeddings)
- [成本計算器](https://openai.com/pricing)

## 🆘 獲取幫助

如有問題：

1. 查看日誌：`storage/logs/laravel.log`
2. 執行診斷：`php artisan knowledge:test-openai`
3. 查看文檔：`docs/AI_KNOWLEDGE_BASE.md`
4. 提交 Issue：https://github.com/ElinksTeam/ElinksBoard/issues

## 🎯 下一步

1. **測試基礎功能**
   - 生成 embeddings
   - 測試語義搜尋
   - 測試 AI 問答

2. **前端整合**
   - 添加搜尋介面
   - 添加聊天介面
   - 添加推薦卡片

3. **監控和最佳化**
   - 設定監控
   - 分析使用情況
   - 最佳化成本

4. **收集反饋**
   - 使用者測試
   - 收集反饋
   - 持續改進
