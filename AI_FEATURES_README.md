# AI-Powered Knowledge Base Features

This document describes the AI features that have been added to ElinksBoard, including semantic search and AI chat capabilities powered by OpenAI.

## Features Overview

### 1. Semantic Search
- Vector-based similarity search for knowledge base articles
- Automatic embedding generation for knowledge articles
- Fallback to keyword search when semantic results are insufficient
- Configurable similarity thresholds

### 2. AI Chat Assistant
- Context-aware chat using knowledge base content
- Conversation history persistence
- Streaming response support
- Source attribution for answers

### 3. Admin Management Tools
- Bulk embedding generation
- Cache management
- Search testing interface

## Installation & Setup

### 1. Environment Configuration

Add the following variables to your `.env` file:

```env
# OpenAI Configuration
OPENAI_API_KEY=your_api_key_here
OPENAI_BASE_URL=                              # Optional: for custom endpoints
OPENAI_MODEL=gpt-4o-mini                      # Chat model
OPENAI_EMBEDDING_MODEL=text-embedding-3-small # Embedding model
OPENAI_SYSTEM_PROMPT=                         # Optional: custom system prompt
```

### 2. Database Migration

Run the migrations to add required database fields:

```bash
php artisan migrate
```

This will:
- Add `embedding` and `embedding_generated_at` fields to the `v2_knowledge` table
- Create the `chat_conversations` table for conversation history

### 3. Generate Embeddings

Generate embeddings for existing knowledge articles:

```bash
# Generate for all articles
php artisan ai:generate-embeddings

# Generate for specific category
php artisan ai:generate-embeddings --category=1

# Generate for specific article
php artisan ai:generate-embeddings --knowledge=123
```

## API Endpoints

### User Endpoints (V1)

#### Semantic Search
```http
POST /api/v1/user/ai/search
Content-Type: application/json
Authorization: Bearer {token}

{
  "query": "How do I configure the VPN?",
  "category_id": 1,           // Optional
  "limit": 10,                // Optional, default: 10
  "min_similarity": 0.7       // Optional, default: 0.7
}
```

Response:
```json
{
  "data": {
    "results": [
      {
        "id": 1,
        "title": "VPN Configuration Guide",
        "category": "Setup",
        "similarity": 0.8542,
        "updated_at": "2025-11-06T20:00:00Z"
      }
    ]
  }
}
```

#### Keyword Search (Fallback)
```http
POST /api/v1/user/ai/keyword-search
Content-Type: application/json
Authorization: Bearer {token}

{
  "query": "VPN setup",
  "category_id": 1,           // Optional
  "limit": 10                 // Optional, default: 10
}
```

#### Create Chat Session
```http
POST /api/v1/user/ai/chat/session
Content-Type: application/json
Authorization: Bearer {token}

{
  "category_id": 1            // Optional
}
```

Response:
```json
{
  "data": {
    "session_id": "550e8400-e29b-41d4-a716-446655440000"
  }
}
```

#### Chat (Non-streaming)
```http
POST /api/v1/user/ai/chat
Content-Type: application/json
Authorization: Bearer {token}

{
  "message": "How do I set up the VPN client?",
  "session_id": "550e8400-e29b-41d4-a716-446655440000",  // Optional
  "category_id": 1                                        // Optional
}
```

Response:
```json
{
  "data": {
    "response": "To set up the VPN client, follow these steps...",
    "session_id": "550e8400-e29b-41d4-a716-446655440000",
    "sources": [
      {
        "id": 1,
        "title": "VPN Configuration Guide",
        "similarity": 0.8542
      }
    ]
  }
}
```

#### Chat (Streaming)
```http
POST /api/v1/user/ai/chat/stream
Content-Type: application/json
Authorization: Bearer {token}

{
  "message": "How do I set up the VPN client?",
  "session_id": "550e8400-e29b-41d4-a716-446655440000",  // Optional
  "category_id": 1                                        // Optional
}
```

Response (Server-Sent Events):
```
data: {"type":"content","data":"To set up"}
data: {"type":"content","data":" the VPN"}
data: {"type":"content","data":" client..."}
data: {"type":"sources","data":[{"id":1,"title":"VPN Configuration Guide","similarity":0.8542}],"session_id":"550e8400-e29b-41d4-a716-446655440000"}
data: {"type":"done"}
```

#### Get Chat Session
```http
GET /api/v1/user/ai/chat/session/{sessionId}
Authorization: Bearer {token}
```

Response:
```json
{
  "data": {
    "session_id": "550e8400-e29b-41d4-a716-446655440000",
    "category_id": 1,
    "messages": [
      {
        "role": "user",
        "content": "How do I set up the VPN?",
        "timestamp": "2025-11-06T20:00:00Z"
      },
      {
        "role": "assistant",
        "content": "To set up the VPN...",
        "timestamp": "2025-11-06T20:00:05Z"
      }
    ],
    "created_at": "2025-11-06T20:00:00Z",
    "updated_at": "2025-11-06T20:00:05Z"
  }
}
```

### Admin Endpoints (V2)

#### Regenerate Embeddings
```http
POST /api/v2/{admin_path}/ai/regenerate-embeddings
Content-Type: application/json
Authorization: Bearer {admin_token}

{
  "category_id": 1,           // Optional: regenerate for specific category
  "knowledge_id": 123         // Optional: regenerate for specific article
}
```

Response:
```json
{
  "data": {
    "stats": {
      "total": 100,
      "success": 98,
      "failed": 2
    },
    "message": "处理完成: 成功 98, 失败 2, 总计 100"
  }
}
```

#### Clear Embedding Cache
```http
POST /api/v2/{admin_path}/ai/clear-embedding-cache
Content-Type: application/json
Authorization: Bearer {admin_token}

{
  "knowledge_id": 123         // Optional: clear cache for specific article
}
```

Response:
```json
{
  "data": {
    "message": "缓存清除成功"
  }
}
```

#### Test Search
```http
POST /api/v2/{admin_path}/ai/test-search
Content-Type: application/json
Authorization: Bearer {admin_token}

{
  "query": "VPN setup",
  "category_id": 1,           // Optional
  "limit": 5                  // Optional, default: 5
}
```

Response:
```json
{
  "data": {
    "results": [
      {
        "id": 1,
        "title": "VPN Configuration Guide",
        "category": "Setup",
        "similarity": 0.8542
      }
    ]
  }
}
```

## Artisan Commands

### Generate Embeddings
```bash
# Generate for all articles
php artisan ai:generate-embeddings

# Generate for specific category
php artisan ai:generate-embeddings --category=1

# Generate for specific article
php artisan ai:generate-embeddings --knowledge=123

# Force regeneration
php artisan ai:generate-embeddings --force
```

### Clear Cache
```bash
# Clear all embedding caches
php artisan ai:clear-cache

# Clear cache for specific article
php artisan ai:clear-cache --knowledge=123
```

### Test AI Features
```bash
# Test embedding generation
php artisan ai:test --embedding

# Test semantic search
php artisan ai:test --search="How to configure VPN?"

# Test AI chat
php artisan ai:test --chat="What is the setup process?"
```

## Architecture

### Service Classes

#### OpenAIService
- Handles direct communication with OpenAI API
- Supports chat, streaming chat, and embedding generation
- Lazy-loads client to avoid initialization errors

#### KnowledgeBaseService
- Manages embedding generation and caching
- Provides semantic search functionality
- Calculates cosine similarity between vectors

#### SemanticSearchService
- High-level search interface
- Implements search with fallback to keyword search
- Manages bulk embedding regeneration

#### AIChatService
- Manages AI chat conversations
- Integrates semantic search for context
- Handles conversation history persistence

### Models

#### Knowledge (Extended)
- Added `embedding` field (JSON array)
- Added `embedding_generated_at` timestamp
- Automatic JSON casting for embedding field

#### ChatConversation (New)
- Stores conversation history
- Links to user and category
- Provides helper methods for message management

### Controllers

#### User Controllers
- `AISearchController`: Handles search requests
- `AIChatController`: Manages chat sessions and messages

#### Admin Controllers
- `AIController`: Provides admin management tools

## Configuration

### System Prompt Customization

The default system prompt can be customized via the `OPENAI_SYSTEM_PROMPT` environment variable or by modifying the `getDefaultSystemPrompt()` method in `AIChatService`.

Default prompt:
```
你是一个专业的客服助手，负责回答用户关于产品和服务的问题。

请遵循以下规则：
1. 基于提供的知识库内容回答问题
2. 如果知识库中没有相关信息，请诚实地告知用户
3. 保持回答简洁、准确、友好
4. 如果问题不清楚，请要求用户提供更多信息
5. 在回答中引用相关的知识库文档编号

请用中文回答问题。
```

### Model Configuration

Models can be configured via environment variables:
- `OPENAI_MODEL`: Chat model (default: `gpt-4o-mini`)
- `OPENAI_EMBEDDING_MODEL`: Embedding model (default: `text-embedding-3-small`)

### Cache Configuration

Embeddings are cached in Redis with:
- Prefix: `kb_embedding:`
- TTL: 86400 seconds (24 hours)

## Performance Considerations

### Embedding Generation
- Batch processing recommended for large knowledge bases
- Use the `ai:generate-embeddings` command during off-peak hours
- Consider rate limits from OpenAI API

### Caching Strategy
- Embeddings are cached for 24 hours
- Clear cache after updating knowledge articles
- Use `ai:clear-cache` command for manual cache management

### Search Optimization
- Adjust `min_similarity` threshold based on your content
- Lower thresholds (0.6-0.7) for broader results
- Higher thresholds (0.8+) for more precise matches

## Troubleshooting

### "OpenAI API key not configured"
- Ensure `OPENAI_API_KEY` is set in `.env`
- Restart the application after updating environment variables

### No search results
- Verify embeddings have been generated: `php artisan ai:generate-embeddings`
- Check if knowledge articles are marked as visible (`show = true`)
- Try lowering the `min_similarity` threshold

### Slow response times
- Check OpenAI API status
- Consider using a faster model (e.g., `gpt-3.5-turbo`)
- Verify Redis cache is working properly

### Chat context not maintained
- Ensure `session_id` is passed in subsequent requests
- Check if `chat_conversations` table exists
- Verify database connection

## Future Enhancements

Potential improvements for future versions:
- Support for multiple embedding models
- Advanced filtering and ranking algorithms
- Multi-language support
- Analytics and usage tracking
- Fine-tuning support
- Custom embedding storage (e.g., vector databases)

## Security Considerations

- API keys should never be exposed to clients
- User authentication required for all endpoints
- Admin endpoints require admin privileges
- Rate limiting recommended for public-facing endpoints
- Conversation history access restricted to owners

## License

This feature is part of ElinksBoard and follows the same license terms.
