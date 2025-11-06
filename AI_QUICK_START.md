# AI Features Quick Start Guide

## Setup (5 minutes)

### 1. Configure Environment
```bash
# Add to .env
OPENAI_API_KEY=sk-your-api-key-here
OPENAI_MODEL=gpt-4o-mini
OPENAI_EMBEDDING_MODEL=text-embedding-3-small
```

### 2. Run Migrations
```bash
php artisan migrate
```

### 3. Generate Embeddings
```bash
php artisan ai:generate-embeddings
```

## Testing

### Test Embedding Generation
```bash
php artisan ai:test --embedding
```

### Test Semantic Search
```bash
php artisan ai:test --search="How to setup VPN?"
```

### Test AI Chat
```bash
php artisan ai:test --chat="What is the installation process?"
```

## Usage Examples

### Frontend Integration (JavaScript)

#### Semantic Search
```javascript
async function searchKnowledge(query) {
  const response = await fetch('/api/v1/user/ai/search', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'Authorization': `Bearer ${token}`
    },
    body: JSON.stringify({
      query: query,
      limit: 10,
      min_similarity: 0.7
    })
  });
  
  const data = await response.json();
  return data.data.results;
}
```

#### AI Chat (Non-streaming)
```javascript
async function chatWithAI(message, sessionId = null) {
  const response = await fetch('/api/v1/user/ai/chat', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'Authorization': `Bearer ${token}`
    },
    body: JSON.stringify({
      message: message,
      session_id: sessionId
    })
  });
  
  const data = await response.json();
  return {
    response: data.data.response,
    sessionId: data.data.session_id,
    sources: data.data.sources
  };
}
```

#### AI Chat (Streaming)
```javascript
async function streamChatWithAI(message, sessionId, onChunk, onComplete) {
  const response = await fetch('/api/v1/user/ai/chat/stream', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'Authorization': `Bearer ${token}`
    },
    body: JSON.stringify({
      message: message,
      session_id: sessionId
    })
  });

  const reader = response.body.getReader();
  const decoder = new TextDecoder();
  let buffer = '';

  while (true) {
    const { done, value } = await reader.read();
    if (done) break;

    buffer += decoder.decode(value, { stream: true });
    const lines = buffer.split('\n');
    buffer = lines.pop();

    for (const line of lines) {
      if (line.startsWith('data: ')) {
        const data = JSON.parse(line.slice(6));
        
        if (data.type === 'content') {
          onChunk(data.data);
        } else if (data.type === 'sources') {
          onComplete(data.data, data.session_id);
        }
      }
    }
  }
}

// Usage
streamChatWithAI(
  'How do I configure the VPN?',
  sessionId,
  (chunk) => {
    // Append chunk to UI
    console.log('Received:', chunk);
  },
  (sources, sessionId) => {
    // Display sources
    console.log('Sources:', sources);
    console.log('Session ID:', sessionId);
  }
);
```

### Backend Integration (PHP)

#### Using Services Directly
```php
use App\Services\AI\SemanticSearchService;
use App\Services\AI\AIChatService;

// Semantic Search
$searchService = app(SemanticSearchService::class);
$results = $searchService->search('VPN setup', 10, null, 0.7);

foreach ($results as $result) {
    echo $result['knowledge']->title . ' - ' . $result['similarity'] . "\n";
}

// AI Chat
$chatService = app(AIChatService::class);
$sessionId = $chatService->createSession($userId, $categoryId);
$response = $chatService->chat('How to setup VPN?', $sessionId);

echo $response['response'];
```

## Common Tasks

### Regenerate Embeddings After Content Update
```bash
# For specific article
php artisan ai:generate-embeddings --knowledge=123

# For entire category
php artisan ai:generate-embeddings --category=1

# For all articles
php artisan ai:generate-embeddings
```

### Clear Cache
```bash
# Clear all
php artisan ai:clear-cache

# Clear specific article
php artisan ai:clear-cache --knowledge=123
```

### Monitor Performance
```bash
# Check logs
tail -f storage/logs/laravel.log | grep -i "openai\|embedding\|semantic"
```

## File Structure

```
app/
├── Console/Commands/AI/
│   ├── GenerateEmbeddings.php
│   ├── ClearEmbeddingCache.php
│   └── TestAIFeatures.php
├── Http/
│   ├── Controllers/
│   │   ├── V1/User/
│   │   │   ├── AISearchController.php
│   │   │   └── AIChatController.php
│   │   └── V2/Admin/
│   │       └── AIController.php
│   └── Routes/
│       ├── V1/UserRoute.php
│       └── V2/AdminRoute.php
├── Models/
│   ├── Knowledge.php (extended)
│   └── ChatConversation.php (new)
└── Services/AI/
    ├── OpenAIService.php
    ├── KnowledgeBaseService.php
    ├── SemanticSearchService.php
    └── AIChatService.php

config/
└── services.php (extended)

database/migrations/
├── 2025_11_07_040527_add_embedding_to_knowledge_table.php
└── 2025_11_07_040606_create_chat_conversations_table.php
```

## API Endpoints Summary

### User Endpoints
- `POST /api/v1/user/ai/search` - Semantic search
- `POST /api/v1/user/ai/keyword-search` - Keyword search
- `POST /api/v1/user/ai/chat/session` - Create chat session
- `POST /api/v1/user/ai/chat` - Chat (non-streaming)
- `POST /api/v1/user/ai/chat/stream` - Chat (streaming)
- `GET /api/v1/user/ai/chat/session/{id}` - Get session

### Admin Endpoints
- `POST /api/v2/{admin}/ai/regenerate-embeddings` - Regenerate embeddings
- `POST /api/v2/{admin}/ai/clear-embedding-cache` - Clear cache
- `POST /api/v2/{admin}/ai/test-search` - Test search

## Troubleshooting

| Issue | Solution |
|-------|----------|
| No API key error | Set `OPENAI_API_KEY` in `.env` |
| No search results | Run `php artisan ai:generate-embeddings` |
| Slow responses | Check OpenAI API status, consider faster model |
| Cache issues | Run `php artisan ai:clear-cache` |
| Migration errors | Ensure database connection is configured |

## Next Steps

1. ✅ Configure environment variables
2. ✅ Run migrations
3. ✅ Generate embeddings
4. ✅ Test features
5. 🔄 Integrate into frontend
6. 🔄 Monitor usage and performance
7. 🔄 Adjust similarity thresholds as needed

## Support

For detailed documentation, see `AI_FEATURES_README.md`
