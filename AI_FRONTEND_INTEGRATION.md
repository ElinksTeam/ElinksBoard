# AI 前端组件集成指南

## 概述

本文档介绍如何在 ElinksBoard 中集成 AI 搜索和聊天组件。提供了三种集成方式：
1. **独立页面** - 完整的 AI 助手页面
2. **可嵌入组件** - 浮动按钮式的搜索和聊天组件
3. **自定义集成** - 通过 API 自定义实现

## 文件说明

### 已创建的文件

```
public/
├── ai-assistant.html              # 完整的 AI 助手页面
├── ai-widgets-demo.html           # 组件演示页面
├── ai-mobile-test.html            # 移动端测试页面
└── assets/
    ├── ai-search-widget.js        # AI 搜索组件
    ├── ai-chat-widget.js          # AI 聊天组件
    └── ai-auth-helper.js          # 认证辅助工具
```

## 方式一：独立页面

### 访问地址
```
https://your-domain.com/ai-assistant.html
```

### 特点
- ✅ 完整的搜索和聊天界面
- ✅ 响应式设计，支持手机和电脑
- ✅ 无需额外配置
- ✅ 可直接链接或嵌入 iframe

### 使用方法

#### 1. 直接访问
用户可以直接访问 `/ai-assistant.html` 使用 AI 功能。

#### 2. 添加导航链接
在主题中添加链接：
```html
<a href="/ai-assistant.html">AI 助手</a>
```

#### 3. iframe 嵌入
```html
<iframe 
    src="/ai-assistant.html" 
    width="100%" 
    height="800px" 
    frameborder="0"
    style="border-radius: 12px;"
></iframe>
```

## 方式二：可嵌入组件

### 演示页面
```
https://your-domain.com/ai-widgets-demo.html
```

### AI 搜索组件

#### 基本使用
```html
<!-- 引入脚本 -->
<script src="/assets/ai-search-widget.js"></script>

<!-- 初始化 -->
<script>
const searchWidget = new AISearchWidget({
    authToken: localStorage.getItem('auth_token'),
    placeholder: '搜索知识库...',
    minSimilarity: 0.7,
    limit: 5
});
</script>
```

#### 配置选项
```javascript
new AISearchWidget({
    // API 配置
    apiBase: '/api/v1/user',              // API 基础路径
    authToken: 'your-token',              // 认证 token
    
    // 搜索配置
    placeholder: '搜索知识库...',         // 输入框占位符
    minSimilarity: 0.7,                   // 最小相似度 (0-1)
    limit: 5,                             // 结果数量
    
    // 回调函数
    onResultClick: function(result) {     // 点击结果时的回调
        console.log('Clicked:', result);
        // 自定义处理逻辑
    }
});
```

#### 自动初始化
```html
<!-- 添加 data 属性即可自动初始化 -->
<div data-ai-search-widget></div>
<script src="/assets/ai-search-widget.js"></script>
```

### AI 聊天组件

#### 基本使用
```html
<!-- 引入脚本 -->
<script src="/assets/ai-chat-widget.js"></script>

<!-- 初始化 -->
<script>
const chatWidget = new AIChatWidget({
    authToken: localStorage.getItem('auth_token'),
    position: 'bottom-right',
    greeting: '你好！有什么可以帮助你的吗？'
});
</script>
```

#### 配置选项
```javascript
new AIChatWidget({
    // API 配置
    apiBase: '/api/v1/user',              // API 基础路径
    authToken: 'your-token',              // 认证 token
    
    // 界面配置
    position: 'bottom-right',             // 位置: bottom-right, bottom-left
    greeting: '你好！我是AI助手...',      // 欢迎消息
    placeholder: '输入消息...',           // 输入框占位符
    
    // 功能配置
    streaming: true                       // 是否启用流式响应
});
```

#### 自动初始化
```html
<!-- 添加 data 属性即可自动初始化 -->
<div data-ai-chat-widget></div>
<script src="/assets/ai-chat-widget.js"></script>
```

## 方式三：主题集成

### 在 Xboard 主题中集成

#### 1. 通过自定义 HTML（推荐）
在后台 **主题配置** → **自定义页脚HTML** 中添加：

```html
<!-- AI 组件 + 认证辅助 -->
<script src="/assets/ai-auth-helper.js"></script>
<script src="/assets/ai-search-widget.js"></script>
<script src="/assets/ai-chat-widget.js"></script>
<script>
(function() {
    // 等待页面加载完成
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initAIWidgets);
    } else {
        initAIWidgets();
    }
    
    function initAIWidgets() {
        // 使用认证辅助工具获取 token
        const token = window.aiAuthHelper ? window.aiAuthHelper.getToken() : 
                     (localStorage.getItem('auth_token') || 
                      localStorage.getItem('authorization') || '');
        
        // 如果未登录，显示提示
        if (!token && window.aiAuthHelper) {
            window.aiAuthHelper.showBanner(
                '⚠️ 您尚未登录，AI 功能可能无法使用。<a href="/" style="color: inherit; font-weight: 600; text-decoration: underline; margin-left: 8px;">立即登录</a>',
                'warning'
            );
        }
        
        // 初始化搜索组件
        new AISearchWidget({
            authToken: token,
            placeholder: '搜索知识库...',
            minSimilarity: 0.7,
            limit: 8
        });
        
        // 初始化聊天组件
        new AIChatWidget({
            authToken: token,
            position: 'bottom-right',
            greeting: '👋 你好！我是 AI 助手，有什么可以帮助你的吗？',
            streaming: false
        });
    }
})();
</script>
```

#### 2. 修改主题文件
编辑 `theme/Xboard/dashboard.blade.php`，在 `</body>` 前添加：

```html
<!-- AI 组件 -->
<script src="/assets/ai-search-widget.js"></script>
<script src="/assets/ai-chat-widget.js"></script>
<script>
window.addEventListener('load', function() {
    const token = localStorage.getItem('auth_token');
    if (token) {
        new AISearchWidget({ authToken: token });
        new AIChatWidget({ authToken: token });
    }
});
</script>
```

### 在管理后台集成

编辑 `resources/views/admin.blade.php`，在 `</body>` 前添加：

```html
<!-- AI 助手 -->
<script src="/assets/ai-chat-widget.js"></script>
<script>
window.addEventListener('load', function() {
    const token = localStorage.getItem('admin_token') || 
                 localStorage.getItem('auth_token');
    if (token) {
        new AIChatWidget({
            authToken: token,
            position: 'bottom-left',
            greeting: '你好！我可以帮你查找文档和解答问题。'
        });
    }
});
</script>
```

## 认证集成

### 认证辅助工具

AI 组件提供了认证辅助工具来处理登录状态检测和 token 管理。

#### 基本使用

```html
<script src="/assets/ai-auth-helper.js"></script>
<script>
// 使用全局实例
const token = window.aiAuthHelper.getToken();
const isLoggedIn = window.aiAuthHelper.isAuthenticated();

// 或创建自定义实例
const authHelper = new AIAuthHelper({
    tokenKeys: ['auth_token', 'authorization'],
    loginUrl: '/login',
    checkInterval: 300000 // 5分钟检查一次
});
</script>
```

#### 配置选项

```javascript
new AIAuthHelper({
    // Token 存储键名（按顺序查找）
    tokenKeys: ['auth_token', 'authorization', 'token'],
    
    // API 基础路径
    apiBase: '/api/v1/user',
    
    // 登录页面 URL
    loginUrl: '/',
    
    // 定期检查间隔（毫秒，0 表示禁用）
    checkInterval: 300000,
    
    // 自定义回调
    onAuthRequired: function() {
        console.log('需要登录');
    },
    
    onAuthExpired: function() {
        console.log('登录已过期');
    }
});
```

#### 主要方法

```javascript
// 获取 token
const token = authHelper.getToken();

// 设置 token
authHelper.setToken('your-token');

// 清除 token
authHelper.clearToken();

// 检查是否已登录
if (authHelper.isAuthenticated()) {
    console.log('已登录');
}

// 验证 token 是否有效
const isValid = await authHelper.validateToken();

// 显示登录对话框
authHelper.handleAuthRequired();

// 显示登录过期对话框
authHelper.handleAuthExpired();

// 显示提示横幅
authHelper.showBanner('提示信息', 'warning'); // warning, error, info, success
```

### 组件认证处理

#### 搜索组件

```javascript
const searchWidget = new AISearchWidget({
    authToken: window.aiAuthHelper.getToken(),
    // ... 其他配置
});

// 组件会自动处理：
// - 未登录时显示提示
// - 401/403 错误时显示登录过期
```

#### 聊天组件

```javascript
const chatWidget = new AIChatWidget({
    authToken: window.aiAuthHelper.getToken(),
    // ... 其他配置
});

// 组件会自动处理：
// - 未登录时显示提示消息
// - 401/403 错误时显示登录过期消息
```

### 完整集成示例

```html
<!DOCTYPE html>
<html>
<head>
    <title>AI 助手</title>
</head>
<body>
    <!-- 引入认证辅助 -->
    <script src="/assets/ai-auth-helper.js"></script>
    
    <!-- 引入 AI 组件 -->
    <script src="/assets/ai-search-widget.js"></script>
    <script src="/assets/ai-chat-widget.js"></script>
    
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // 检查登录状态
        if (!window.aiAuthHelper.isAuthenticated()) {
            window.aiAuthHelper.showBanner(
                '您尚未登录，部分功能可能无法使用。<a href="/login">立即登录</a>',
                'warning'
            );
        }
        
        // 获取 token
        const token = window.aiAuthHelper.getToken();
        
        // 初始化组件
        new AISearchWidget({ authToken: token });
        new AIChatWidget({ authToken: token });
        
        // 定期验证 token（可选）
        setInterval(async () => {
            const isValid = await window.aiAuthHelper.validateToken();
            if (!isValid) {
                console.log('Token 已失效');
            }
        }, 300000); // 每5分钟检查一次
    });
    </script>
</body>
</html>
```

### 错误处理

#### 未登录处理

```javascript
// 方式1：显示对话框（阻塞式）
window.aiAuthHelper.handleAuthRequired();

// 方式2：显示横幅（非阻塞式）
window.aiAuthHelper.showBanner(
    '请先登录以使用此功能',
    'warning'
);

// 方式3：自定义处理
const authHelper = new AIAuthHelper({
    onAuthRequired: function() {
        // 自定义逻辑
        alert('请先登录');
        window.location.href = '/login';
    }
});
```

#### 登录过期处理

```javascript
// 方式1：显示对话框
window.aiAuthHelper.handleAuthExpired();

// 方式2：自定义处理
const authHelper = new AIAuthHelper({
    onAuthExpired: function() {
        // 清除本地数据
        localStorage.clear();
        // 跳转到登录页
        window.location.href = '/login?expired=1';
    }
});
```

#### API 错误处理

```javascript
async function callAPI() {
    try {
        const response = await fetch('/api/v1/user/ai/search', {
            method: 'POST',
            headers: {
                'Authorization': `Bearer ${window.aiAuthHelper.getToken()}`
            },
            body: JSON.stringify({ query: 'test' })
        });
        
        // 检查认证错误
        if (response.status === 401 || response.status === 403) {
            window.aiAuthHelper.handleAuthExpired();
            return;
        }
        
        const data = await response.json();
        return data;
    } catch (error) {
        console.error('API 调用失败:', error);
    }
}
```

## 方式四：自定义实现

### 使用 API 直接调用

#### 搜索 API
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

#### 聊天 API
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

#### 流式聊天 API
```javascript
async function streamChat(message, sessionId, onChunk, onComplete) {
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
```

## 样式自定义

### 修改颜色主题

#### 搜索组件
```css
/* 修改主色调 */
.ai-search-trigger {
    background: linear-gradient(135deg, #your-color 0%, #your-color-dark 100%);
}

.ai-search-btn {
    background: #your-color;
}

.ai-search-similarity {
    background: #your-color;
}
```

#### 聊天组件
```css
/* 修改主色调 */
.ai-chat-trigger {
    background: linear-gradient(135deg, #your-color 0%, #your-color-dark 100%);
}

.ai-chat-header {
    background: linear-gradient(135deg, #your-color 0%, #your-color-dark 100%);
}

.ai-chat-send-btn {
    background: #your-color;
}
```

### 修改位置
```javascript
// 搜索组件 - 修改 CSS
.ai-search-widget {
    bottom: 24px;
    left: 24px;  /* 改为左下角 */
}

// 聊天组件 - 通过配置
new AIChatWidget({
    position: 'bottom-left'  // 或 'bottom-right'
});
```

### 隐藏组件
```css
/* 在特定页面隐藏 */
.specific-page .ai-search-widget,
.specific-page .ai-chat-widget {
    display: none !important;
}
```

## 响应式设计

组件已内置响应式设计：

### 桌面端 (>640px)
- 搜索面板：400px 宽
- 聊天窗口：380px 宽 × 600px 高
- 浮动按钮：56px × 56px

### 移动端 (≤640px)
- 搜索面板：全屏宽度
- 聊天窗口：全屏宽度和高度
- 浮动按钮：48px × 48px

## 性能优化

### 懒加载
```html
<!-- 延迟加载组件 -->
<script>
setTimeout(function() {
    const script1 = document.createElement('script');
    script1.src = '/assets/ai-search-widget.js';
    document.body.appendChild(script1);
    
    const script2 = document.createElement('script');
    script2.src = '/assets/ai-chat-widget.js';
    document.body.appendChild(script2);
}, 2000);
</script>
```

### 条件加载
```javascript
// 仅在特定页面加载
if (window.location.pathname.includes('/knowledge')) {
    const script = document.createElement('script');
    script.src = '/assets/ai-search-widget.js';
    document.body.appendChild(script);
}
```

## 故障排查

### 组件不显示
1. 检查脚本是否正确加载
2. 查看浏览器控制台是否有错误
3. 确认 z-index 没有被其他元素覆盖

### 搜索/聊天失败
1. 检查 authToken 是否正确
2. 确认 API 端点可访问
3. 查看网络请求的响应状态

### 样式冲突
1. 使用浏览器开发工具检查 CSS
2. 增加选择器优先级
3. 使用 `!important` 覆盖样式

## 浏览器兼容性

### 支持的浏览器
- ✅ Chrome 90+
- ✅ Firefox 88+
- ✅ Safari 14+
- ✅ Edge 90+
- ✅ 移动端浏览器

### 不支持的功能
- ❌ IE 11 及以下

## 示例项目

### 完整示例
查看 `/ai-widgets-demo.html` 获取完整的集成示例。

### 在线演示
- 独立页面：`/ai-assistant.html`
- 组件演示：`/ai-widgets-demo.html`

## API 文档

详细的 API 文档请参考：
- [AI_FEATURES_README.md](AI_FEATURES_README.md) - 完整功能文档
- [AI_QUICK_START.md](AI_QUICK_START.md) - 快速开始指南

## 更新日志

### v1.0.0 (2025-11-06)
- ✅ 初始版本发布
- ✅ AI 搜索组件
- ✅ AI 聊天组件
- ✅ 独立页面
- ✅ 响应式设计

## 技术支持

如有问题，请查看：
1. 浏览器控制台错误信息
2. 网络请求详情
3. API 响应内容

## 许可证

本组件遵循 ElinksBoard 项目的许可证。
