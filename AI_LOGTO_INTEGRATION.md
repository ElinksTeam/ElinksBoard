# AI 组件 Logto 认证集成指南

## 概述

ElinksBoard 使用 Logto 作为认证系统。AI 组件已完全集成 Logto 认证流程。

## Logto 认证流程

### 1. 用户登录流程

```
用户访问页面
    ↓
检测到未登录
    ↓
调用 /api/v1/passport/auth/logto/sign-in
    ↓
获取 Logto 登录 URL
    ↓
重定向到 Logto 登录页面
    ↓
用户在 Logto 完成登录
    ↓
Logto 回调到 /api/v1/passport/auth/logto/callback
    ↓
后端创建/更新用户并生成 Sanctum token
    ↓
返回 auth_data (包含 token 和用户信息)
    ↓
前端保存到 localStorage
    ↓
用户可以使用 AI 功能
```

### 2. Token 存储结构

Logto 认证成功后，`auth_data` 存储在 localStorage：

```javascript
{
    "token": "1|xxxxx...",  // Sanctum token
    "is_admin": false,
    "user": {
        "id": 1,
        "email": "user@example.com",
        "uuid": "xxx-xxx-xxx",
        "is_admin": false,
        "is_staff": false,
        "balance": 0,
        "transfer_enable": 0,
        "expired_at": null
    }
}
```

## AI 组件集成

### 方式一：自动集成（推荐）

AI 组件会自动从 `auth_data` 中提取 token：

```html
<!-- 引入认证辅助（已支持 Logto） -->
<script src="/assets/ai-auth-helper.js"></script>

<!-- 引入 AI 组件 -->
<script src="/assets/ai-search-widget.js"></script>
<script src="/assets/ai-chat-widget.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // 自动从 auth_data 获取 token
    const token = window.aiAuthHelper.getToken();
    
    // 初始化组件
    new AISearchWidget({ authToken: token });
    new AIChatWidget({ authToken: token });
});
</script>
```

### 方式二：手动提取 Token

```javascript
// 从 auth_data 提取 token
function getAuthToken() {
    const authDataStr = localStorage.getItem('auth_data');
    if (authDataStr) {
        try {
            const authData = JSON.parse(authDataStr);
            return authData.token;
        } catch (e) {
            console.error('Failed to parse auth_data:', e);
        }
    }
    return null;
}

// 使用 token
const token = getAuthToken();
new AISearchWidget({ authToken: token });
new AIChatWidget({ authToken: token });
```

### 方式三：使用认证辅助工具

```javascript
// 创建认证辅助实例（已配置 Logto）
const authHelper = new AIAuthHelper({
    useLogto: true,  // 启用 Logto 支持（默认）
    logtoSignInUrl: '/api/v1/passport/auth/logto/sign-in',
    tokenKeys: ['auth_data', 'auth_token', 'authorization']
});

// 获取 token
const token = authHelper.getToken();

// 获取用户信息
const userInfo = authHelper.getUserInfo();
console.log('User:', userInfo);

// 检查是否是管理员
if (authHelper.isAdmin()) {
    console.log('User is admin');
}

// 检查登录状态
if (!authHelper.isAuthenticated()) {
    // 自动重定向到 Logto 登录
    authHelper.handleAuthRequired();
}
```

## 完整集成示例

### 在主题中集成

```html
<!-- 在 theme/Xboard/dashboard.blade.php 或自定义 HTML 中 -->
<script src="/assets/ai-auth-helper.js"></script>
<script src="/assets/ai-search-widget.js"></script>
<script src="/assets/ai-chat-widget.js"></script>

<script>
(function() {
    // 等待页面加载
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initAI);
    } else {
        initAI();
    }
    
    function initAI() {
        // 使用 Logto 认证辅助
        const authHelper = window.aiAuthHelper;
        const token = authHelper.getToken();
        
        // 检查登录状态
        if (!token) {
            // 显示友好提示
            authHelper.showBanner(
                '💡 登录后可使用 AI 搜索和聊天功能 <a href="#" onclick="window.aiAuthHelper.handleAuthRequired(); return false;" style="color: inherit; font-weight: 600; text-decoration: underline; margin-left: 8px;">立即登录</a>',
                'info'
            );
        }
        
        // 初始化 AI 组件
        new AISearchWidget({
            authToken: token,
            placeholder: '搜索知识库...',
            minSimilarity: 0.7,
            limit: 8
        });
        
        new AIChatWidget({
            authToken: token,
            position: 'bottom-right',
            greeting: '👋 你好！我是 AI 助手，有什么可以帮助你的吗？',
            streaming: false
        });
        
        // 监听 token 变化（登录/登出）
        window.addEventListener('storage', function(e) {
            if (e.key === 'auth_data') {
                if (e.newValue) {
                    console.log('User logged in');
                    // 可以重新初始化组件或刷新页面
                    location.reload();
                } else {
                    console.log('User logged out');
                }
            }
        });
    }
})();
</script>
```

### 处理 Logto 回调

如果你的页面需要处理 Logto 回调：

```javascript
// 检查是否是从 Logto 回调返回
const urlParams = new URLSearchParams(window.location.search);
if (urlParams.has('code') && urlParams.has('state')) {
    // 这是 Logto 回调
    console.log('Processing Logto callback...');
    
    // 后端会自动处理回调并设置 auth_data
    // 前端只需要等待并重定向到原页面
    const returnUrl = sessionStorage.getItem('logto_return_url') || '/';
    sessionStorage.removeItem('logto_return_url');
    
    // 等待一下确保 auth_data 已设置
    setTimeout(() => {
        window.location.href = returnUrl;
    }, 1000);
}
```

## 认证状态检查

### 检查用户是否登录

```javascript
// 方式1：使用认证辅助
if (window.aiAuthHelper.isAuthenticated()) {
    console.log('User is logged in');
}

// 方式2：检查 auth_data
const authData = localStorage.getItem('auth_data');
if (authData) {
    console.log('User is logged in');
}

// 方式3：调用 API 检查
async function checkAuth() {
    try {
        const response = await fetch('/api/v1/passport/auth/logto/check');
        const data = await response.json();
        return data.data.is_authenticated;
    } catch (error) {
        console.error('Failed to check auth:', error);
        return false;
    }
}
```

### 获取用户信息

```javascript
// 方式1：从 localStorage
const authDataStr = localStorage.getItem('auth_data');
if (authDataStr) {
    const authData = JSON.parse(authDataStr);
    console.log('User:', authData.user);
    console.log('Is Admin:', authData.is_admin);
}

// 方式2：使用认证辅助
const userInfo = window.aiAuthHelper.getUserInfo();
console.log('User:', userInfo);

// 方式3：调用 API
async function getUserInfo() {
    try {
        const token = window.aiAuthHelper.getToken();
        const response = await fetch('/api/v1/user/info', {
            headers: {
                'Authorization': `Bearer ${token}`
            }
        });
        const data = await response.json();
        return data.data;
    } catch (error) {
        console.error('Failed to get user info:', error);
        return null;
    }
}
```

## 登出处理

### 完整登出流程

```javascript
async function logout() {
    try {
        const token = window.aiAuthHelper.getToken();
        
        // 调用后端登出 API
        const response = await fetch('/api/v1/passport/auth/logto/sign-out', {
            method: 'POST',
            headers: {
                'Authorization': `Bearer ${token}`
            }
        });
        
        const data = await response.json();
        
        // 清除本地 token
        window.aiAuthHelper.clearToken();
        
        // 重定向到 Logto 登出页面
        if (data.data && data.data.sign_out_url) {
            window.location.href = data.data.sign_out_url;
        } else {
            // 回退：直接刷新页面
            window.location.href = '/';
        }
    } catch (error) {
        console.error('Logout failed:', error);
        // 即使失败也清除本地 token
        window.aiAuthHelper.clearToken();
        window.location.href = '/';
    }
}
```

## Token 过期处理

### 自动检测和处理

```javascript
// 认证辅助会自动处理 401/403 错误
// 组件在收到这些错误时会：
// 1. 清除本地 token
// 2. 显示登录过期提示
// 3. 引导用户重新登录

// 你也可以手动处理
async function callAPI() {
    const token = window.aiAuthHelper.getToken();
    
    try {
        const response = await fetch('/api/v1/user/ai/search', {
            method: 'POST',
            headers: {
                'Authorization': `Bearer ${token}`,
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ query: 'test' })
        });
        
        if (response.status === 401 || response.status === 403) {
            // Token 已过期
            window.aiAuthHelper.handleAuthExpired();
            return null;
        }
        
        return await response.json();
    } catch (error) {
        console.error('API call failed:', error);
        return null;
    }
}
```

### 定期验证 Token

```javascript
// 每5分钟验证一次 token
setInterval(async () => {
    const isValid = await window.aiAuthHelper.validateToken();
    if (!isValid) {
        console.log('Token expired, redirecting to login...');
        window.aiAuthHelper.handleAuthExpired();
    }
}, 300000);
```

## 管理员功能

### 检查管理员权限

```javascript
// 检查是否是管理员
if (window.aiAuthHelper.isAdmin()) {
    console.log('User is admin');
    // 显示管理员功能
}

// 或从 auth_data 检查
const authDataStr = localStorage.getItem('auth_data');
if (authDataStr) {
    const authData = JSON.parse(authDataStr);
    if (authData.is_admin) {
        console.log('User is admin');
    }
}
```

### 管理员专用功能

```javascript
// 只为管理员显示某些功能
document.addEventListener('DOMContentLoaded', function() {
    if (window.aiAuthHelper.isAdmin()) {
        // 显示管理员专用的 AI 功能
        document.getElementById('admin-ai-features').style.display = 'block';
    }
});
```

## 调试和故障排查

### 检查认证状态

```javascript
// 在浏览器控制台运行
console.log('Auth Data:', localStorage.getItem('auth_data'));
console.log('Token:', window.aiAuthHelper.getToken());
console.log('Is Authenticated:', window.aiAuthHelper.isAuthenticated());
console.log('User Info:', window.aiAuthHelper.getUserInfo());
console.log('Is Admin:', window.aiAuthHelper.isAdmin());
```

### 常见问题

#### 1. Token 未找到

**问题：** AI 组件提示未登录

**检查：**
```javascript
// 检查 auth_data 是否存在
console.log(localStorage.getItem('auth_data'));

// 检查是否在 Logto 回调页面
console.log(window.location.search);
```

**解决：**
- 确认已完成 Logto 登录流程
- 检查 Logto 回调是否正确处理
- 清除浏览器缓存后重新登录

#### 2. Token 格式错误

**问题：** API 返回 401 错误

**检查：**
```javascript
const authData = JSON.parse(localStorage.getItem('auth_data'));
console.log('Token format:', authData.token);
// 应该是: "1|xxxxx..."
```

**解决：**
- 确认 token 格式正确
- 重新登录获取新 token

#### 3. 跨域问题

**问题：** 无法调用 Logto API

**检查：**
```javascript
// 检查 Logto 配置
console.log('Logto Sign-In URL:', '/api/v1/passport/auth/logto/sign-in');
```

**解决：**
- 确认 Logto 配置正确
- 检查 CORS 设置

## 最佳实践

### 1. 统一使用认证辅助

```javascript
// ✅ 推荐
const token = window.aiAuthHelper.getToken();

// ❌ 不推荐
const token = localStorage.getItem('auth_token');
```

### 2. 处理登录状态变化

```javascript
// 监听 storage 事件
window.addEventListener('storage', function(e) {
    if (e.key === 'auth_data') {
        // 登录状态改变
        location.reload();
    }
});
```

### 3. 优雅降级

```javascript
// 未登录时显示友好提示，而不是直接阻止
if (!window.aiAuthHelper.isAuthenticated()) {
    // 显示提示横幅
    window.aiAuthHelper.showBanner('登录后可使用更多功能', 'info');
} else {
    // 初始化完整功能
    initFullFeatures();
}
```

### 4. 错误处理

```javascript
// 始终处理 API 错误
async function callAPI() {
    try {
        const response = await fetch('/api/...');
        if (!response.ok) {
            if (response.status === 401) {
                window.aiAuthHelper.handleAuthExpired();
            }
            throw new Error('API call failed');
        }
        return await response.json();
    } catch (error) {
        console.error('Error:', error);
        // 显示用户友好的错误消息
        return null;
    }
}
```

## 安全注意事项

1. **Token 存储**
   - Token 存储在 localStorage 中
   - 不要在 URL 中传递 token
   - 不要在日志中记录 token

2. **Token 传输**
   - 始终使用 HTTPS
   - Token 通过 Authorization header 传递
   - 格式：`Bearer {token}`

3. **Token 生命周期**
   - Token 由 Sanctum 管理
   - 定期验证 token 有效性
   - 登出时清除所有 token

4. **权限检查**
   - 前端检查仅用于 UI 显示
   - 后端必须验证所有权限
   - 不要信任前端的权限判断

## 总结

ElinksBoard 的 AI 组件已完全集成 Logto 认证系统：

- ✅ 自动从 `auth_data` 提取 token
- ✅ 支持 Logto 登录流程
- ✅ 自动处理 token 过期
- ✅ 友好的用户提示
- ✅ 完整的错误处理
- ✅ 管理员权限支持

只需引入组件脚本，即可自动集成 Logto 认证！
