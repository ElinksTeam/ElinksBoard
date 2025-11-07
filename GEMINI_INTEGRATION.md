# Google Gemini API 整合指南

## 概述

ElinksBoard 現在支援使用 Google Gemini API 作為 AI 知識庫的替代方案。Gemini 提供強大的語言模型和嵌入向量生成能力，可與 OpenAI 服務無縫切換。

## 功能特點

- ✅ **多提供商支援**：可在 OpenAI 和 Gemini 之間切換
- ✅ **完整 API 支援**：聊天、串流、嵌入向量生成
- ✅ **批次處理**：支援批次生成嵌入向量
- ✅ **統一介面**：透過 `AIProviderInterface` 提供一致的 API
- ✅ **靈活配置**：透過環境變數輕鬆配置

## 快速開始

### 1. 獲取 Gemini API Key

1. 訪問 [Google AI Studio](https://aistudio.google.com/)
2. 登入您的 Google 帳戶
3. 在 API Keys 頁面創建新的 API Key
4. 複製 API Key 以供後續使用

### 2. 環境配置

在 `.env` 文件中添加以下配置：

```env
# Gemini Configuration
GEMINI_API_KEY=your_gemini_api_key_here
GEMINI_MODEL=gemini-2.0-flash-exp
GEMINI_EMBEDDING_MODEL=text-embedding-004

# AI Provider Configuration
AI_DEFAULT_PROVIDER=gemini  # 或 'openai'
```

### 3. 可用模型

#### 聊天模型
- `gemini-2.0-flash-exp` - 快速且高效的最新模型（推薦）
- `gemini-1.5-pro` - 更強大但較慢的模型
- `gemini-1.5-flash` - 平衡的選擇

#### 嵌入模型
- `text-embedding-004` - 最新的嵌入模型（推薦）
- `embedding-001` - 早期版本

## 使用方式

### 方式 1：使用預設提供商

系統會自動使用 `AI_DEFAULT_PROVIDER` 配置的提供商：

```php
use App\Services\AI\KnowledgeBaseService;
use App\Services\AI\AIChatService;

// 使用預設提供商（從配置讀取）
$knowledgeService = app(KnowledgeBaseService::class);
$chatService = app(AIChatService::class);
```

### 方式 2：明確指定提供商

您可以在創建服務時指定使用哪個提供商：

```php
use App\Services\AI\KnowledgeBaseService;
use App\Services\AI\AIChatService;

// 明確使用 Gemini
$knowledgeService = new KnowledgeBaseService('gemini');
$chatService = new AIChatService(
    $knowledgeService,
    'gemini'
);

// 明確使用 OpenAI
$knowledgeService = new KnowledgeBaseService('openai');
$chatService = new AIChatService(
    $knowledgeService,
    'openai'
);
```

### 方式 3：使用工廠模式

直接使用 `AIProviderFactory`：

```php
use App\Services\AI\AIProviderFactory;

// 獲取預設提供商
$provider = AIProviderFactory::default();

// 獲取特定提供商
$geminiProvider = AIProviderFactory::make('gemini');
$openaiProvider = AIProviderFactory::make('openai');

// 使用提供商
$response = $provider->chat([
    ['role' => 'user', 'content' => '你好！']
]);

$embedding = $provider->createEmbedding('要嵌入的文本');
```

## API 使用範例

### 聊天對話

```php
use App\Services\AI\AIProviderFactory;

$provider = AIProviderFactory::make('gemini');

$messages = [
    ['role' => 'system', 'content' => '你是一個專業的客服助手'],
    ['role' => 'user', 'content' => '訂閱連結在哪裡找？']
];

$response = $provider->chat($messages);
echo $response;
```

### 串流對話

```php
use App\Services\AI\AIProviderFactory;

$provider = AIProviderFactory::make('gemini');

$messages = [
    ['role' => 'user', 'content' => '請詳細說明訂閱流程']
];

$provider->streamChat($messages, function($chunk) {
    echo $chunk;
    flush();
});
```

### 生成嵌入向量

```php
use App\Services\AI\AIProviderFactory;

$provider = AIProviderFactory::make('gemini');

// 單個文本
$embedding = $provider->createEmbedding('訂閱連結設定指南');

// 批次處理
$texts = [
    '訂閱連結設定指南',
    '客戶端配置教學',
    '常見問題解答'
];
$embeddings = $provider->createBatchEmbeddings($texts);
```

### 語義搜尋

```php
use App\Services\AI\KnowledgeBaseService;

$service = new KnowledgeBaseService('gemini');

$results = $service->semanticSearch(
    query: '如何設定訂閱連結',
    limit: 5,
    categoryId: null
);

foreach ($results as $result) {
    echo "標題: {$result['knowledge']->title}\n";
    echo "相似度: {$result['similarity']}\n\n";
}
```

## 成本比較

### Gemini 定價（截至 2024）

#### 免費額度
- **Gemini 2.0 Flash**: 每天 1500 次請求
- **Text Embedding**: 每天 1500 次請求

#### 付費定價
- **Gemini 2.0 Flash**: 
  - Input: $0.075 / 1M tokens
  - Output: $0.30 / 1M tokens
- **Gemini 1.5 Pro**:
  - Input: $1.25 / 1M tokens
  - Output: $5.00 / 1M tokens
- **Text Embedding**: $0.025 / 1M tokens

### 與 OpenAI 比較

| 功能 | Gemini (Flash) | OpenAI (GPT-4o-mini) |
|------|----------------|----------------------|
| Input 成本 | $0.075/1M | $0.15/1M |
| Output 成本 | $0.30/1M | $0.60/1M |
| Embedding | $0.025/1M | $0.02/1M |
| 免費額度 | ✅ 1500/day | ❌ 無 |
| 速度 | ⚡ 非常快 | ⚡ 快 |

**結論**：Gemini 在成本和免費額度方面更具優勢。

## 配置最佳實踐

### 1. 生產環境配置

```env
# 推薦使用 Gemini 以節省成本
AI_DEFAULT_PROVIDER=gemini
GEMINI_API_KEY=your_production_key
GEMINI_MODEL=gemini-2.0-flash-exp
GEMINI_EMBEDDING_MODEL=text-embedding-004
```

### 2. 開發環境配置

```env
# 可使用 Gemini 免費額度進行開發
AI_DEFAULT_PROVIDER=gemini
GEMINI_API_KEY=your_dev_key
GEMINI_MODEL=gemini-2.0-flash-exp
```

### 3. 混合使用策略

您可以針對不同場景使用不同的提供商：

```php
// 聊天使用 Gemini（成本較低）
$chatService = new AIChatService(
    app(KnowledgeBaseService::class),
    'gemini'
);

// 重要的嵌入向量使用 OpenAI（可能更準確）
$embeddingService = new KnowledgeBaseService('openai');
```

## 遷移指南

### 從 OpenAI 遷移到 Gemini

如果您現有系統使用 OpenAI，遷移到 Gemini 非常簡單：

1. **更新環境變數**：
   ```env
   AI_DEFAULT_PROVIDER=gemini
   GEMINI_API_KEY=your_key
   ```

2. **重新生成嵌入向量**（可選但推薦）：
   ```bash
   php artisan knowledge:generate-embeddings --all
   ```

3. **測試功能**：
   ```bash
   php artisan knowledge:test-ai
   ```

### 並行使用兩個提供商

您可以保留兩個提供商的配置，根據需要切換：

```env
# OpenAI 配置
OPENAI_API_KEY=your_openai_key
OPENAI_MODEL=gpt-4o-mini

# Gemini 配置
GEMINI_API_KEY=your_gemini_key
GEMINI_MODEL=gemini-2.0-flash-exp

# 預設使用 Gemini
AI_DEFAULT_PROVIDER=gemini
```

## 故障排除

### 問題 1：API Key 錯誤

**症狀**：`Gemini API key not configured`

**解決方案**：
1. 檢查 `.env` 文件中是否設置了 `GEMINI_API_KEY`
2. 確保環境變數已加載：`php artisan config:cache`
3. 驗證 API Key 是否正確

### 問題 2：模型不可用

**症狀**：模型請求失敗

**解決方案**：
1. 檢查您使用的模型名稱是否正確
2. 某些模型可能需要 API 權限
3. 訪問 [Google AI Studio](https://aistudio.google.com/) 檢查可用模型

### 問題 3：速率限制

**症狀**：`Rate limit exceeded`

**解決方案**：
1. 免費用戶每天 1500 次請求
2. 考慮升級到付費計劃
3. 實施請求快取策略
4. 使用批次處理減少請求數量

### 問題 4：回應格式不同

**症狀**：Gemini 回應與 OpenAI 不同

**說明**：這是正常的，不同模型有不同的回應風格。`GeminiService` 已經處理了格式轉換，確保統一的介面。

## 進階功能

### 自定義系統提示詞

Gemini 支援系統指令（System Instructions）：

```php
$provider = AIProviderFactory::make('gemini');

$messages = [
    [
        'role' => 'system', 
        'content' => '你是一個技術支援專家，專注於網路代理服務。請用簡潔、專業的語氣回答問題。'
    ],
    ['role' => 'user', 'content' => '如何配置 V2Ray？']
];

$response = $provider->chat($messages);
```

### 多輪對話

```php
$provider = AIProviderFactory::make('gemini');

$conversation = [
    ['role' => 'system', 'content' => '你是一個客服助手'],
    ['role' => 'user', 'content' => '我想購買訂閱'],
    ['role' => 'assistant', 'content' => '好的，請問您需要什麼類型的訂閱？'],
    ['role' => 'user', 'content' => '月付的就可以']
];

$response = $provider->chat($conversation);
```

## 性能最佳化

### 1. 使用快取

```php
use Illuminate\Support\Facades\Cache;

$cacheKey = "gemini_chat:" . md5($query);
$response = Cache::remember($cacheKey, 3600, function() use ($query) {
    $provider = AIProviderFactory::make('gemini');
    return $provider->chat([
        ['role' => 'user', 'content' => $query]
    ]);
});
```

### 2. 批次嵌入生成

```php
// 不好的做法：逐個生成
foreach ($texts as $text) {
    $embedding = $provider->createEmbedding($text);
}

// 好的做法：批次生成
$embeddings = $provider->createBatchEmbeddings($texts);
```

### 3. 非同步處理

```php
use Illuminate\Support\Facades\Queue;

// 將大量嵌入生成任務放入佇列
dispatch(new GenerateEmbeddingsJob($knowledgeIds, 'gemini'));
```

## 監控與日誌

Gemini 服務會自動記錄錯誤到 Laravel 日誌：

```bash
# 查看 Gemini 相關日誌
tail -f storage/logs/laravel.log | grep "Gemini"
```

## 相關資源

- [Google AI Studio](https://aistudio.google.com/) - 獲取 API Key
- [Gemini API 文檔](https://ai.google.dev/api) - 官方 API 參考
- [Gemini PHP Client](https://github.com/google-gemini-php/client) - PHP 客戶端文檔
- [定價資訊](https://ai.google.dev/pricing) - 最新定價信息

## 總結

Google Gemini 為 ElinksBoard 提供了一個強大且經濟實惠的 AI 解決方案。通過統一的介面設計，您可以輕鬆在不同的 AI 提供商之間切換，選擇最適合您需求的方案。

如有問題或建議，請訪問：
- GitHub Issues: https://github.com/ElinksTeam/ElinksBoard/issues
- 文檔: [AI_KNOWLEDGE_QUICKSTART.md](./AI_KNOWLEDGE_QUICKSTART.md)
