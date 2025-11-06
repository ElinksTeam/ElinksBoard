# Logto 集成 - 快速设置指南

## ⚠️ 重要变更

**此版本移除了传统的邮箱/密码登录，所有用户认证都专门使用 Logto。**

- ✅ 安装期间**必须**配置 Logto
- ✅ **首次登录的用户将自动成为管理员**
- ✅ 管理员可以在后台面板中修改 Logto 设置
- ✅ 传统登录路由已被移除
- ✅ 所有用户（包括管理员）都通过 Logto 认证
- ⚠️ **安全提示：安装后立即完成首次登录！**

## 🚀 快速开始（5 分钟）

### 步骤 1：安装依赖

```bash
composer install
```

Logto SDK (`logto/sdk`) 已添加到 `composer.json`。

### 步骤 2：配置环境

复制 `.env.example` 到 `.env`（如果尚未完成）并添加：

```env
# Logto Authentication
LOGTO_ENDPOINT=https://your-logto.app
LOGTO_APP_ID=your_app_id
LOGTO_APP_SECRET=your_app_secret
LOGTO_REDIRECT_URI=${APP_URL}/api/v1/passport/auth/logto/callback
LOGTO_POST_LOGOUT_REDIRECT_URI=${APP_URL}
LOGTO_AUTO_CREATE_USER=true
LOGTO_AUTO_UPDATE_USER=true
```

### 步骤 3：运行数据库迁移

```bash
php artisan migrate
```

这将向用户表添加 `logto_sub` 和 `auth_provider` 字段。

### 步骤 4：配置 Logto 控制台

1. **创建应用程序**
   - 访问 [Logto 控制台](https://cloud.logto.io)（或您的自托管实例）
   - 点击 **Applications** → **Create application**
   - 选择 **Traditional Web Application**
   - 选择 **PHP** 框架

2. **配置重定向 URI**
   
   在应用程序设置中添加这些 URI：
   
   **重定向 URI：**
   ```
   http://localhost:3000/api/v1/passport/auth/logto/callback
   https://your-domain.com/api/v1/passport/auth/logto/callback
   ```
   
   **登出后重定向 URI：**
   ```
   http://localhost:3000
   https://your-domain.com
   ```

3. **复制凭据**
   
   从应用程序详情页面复制：
   - **App ID** → 更新 `.env` 中的 `LOGTO_APP_ID`
   - **App Secret** → 更新 `.env` 中的 `LOGTO_APP_SECRET`
   - **Endpoint** → 更新 `.env` 中的 `LOGTO_ENDPOINT`

### 步骤 5：完成首次登录（关键！）

**⚠️ 重要：首次登录的用户将自动成为管理员！**

1. 安装后立即访问您的站点
2. 点击"使用 Logto 登录"
3. 完成 Logto 认证
4. 您将自动获得管理员权限
5. 后续用户将是普通用户

**安全提示：** 不要延迟此步骤！任何完成首次登录的人都将成为管理员。

### 步骤 6：测试集成

#### 选项 A：使用 cURL

```bash
# 获取登录 URL
curl http://localhost/api/v1/passport/auth/logto/sign-in

# 响应将包含 sign_in_url - 在浏览器中打开它
```

#### 选项 B：使用浏览器

1. 访问：`http://localhost/api/v1/passport/auth/logto/sign-in`
2. 从响应中复制 `sign_in_url`
3. 在浏览器中打开该 URL
4. 完成 Logto 登录流程
5. 您将被重定向回来并获得认证数据

## 📋 可用的 API 端点

| 方法 | 端点 | 描述 |
|--------|----------|-------------|
| GET | `/api/v1/passport/auth/logto/sign-in` | 获取 Logto 登录 URL |
| GET | `/api/v1/passport/auth/logto/callback` | 处理 OIDC 回调 |
| POST | `/api/v1/passport/auth/logto/sign-out` | 登出并获取登出 URL |
| GET | `/api/v1/passport/auth/logto/userinfo` | 获取当前用户信息 |
| GET | `/api/v1/passport/auth/logto/check` | 检查认证状态 |

## 🎨 前端集成

### Vue 3 示例

创建 Logto 认证的 composable：

```typescript
// composables/useLogtoAuth.ts
import { ref } from 'vue'
import axios from 'axios'

export function useLogtoAuth() {
  const isAuthenticated = ref(false)
  const user = ref(null)
  
  async function signIn() {
    const { data } = await axios.get('/api/v1/passport/auth/logto/sign-in')
    window.location.href = data.data.sign_in_url
  }
  
  async function handleCallback() {
    const { data } = await axios.get(
      '/api/v1/passport/auth/logto/callback' + window.location.search
    )
    
    localStorage.setItem('auth_token', data.data.auth_data)
    localStorage.setItem('user', JSON.stringify(data.data.user))
    
    isAuthenticated.value = true
    user.value = data.data.user
    
    return data.data.user
  }
  
  async function signOut() {
    const { data } = await axios.post('/api/v1/passport/auth/logto/sign-out')
    
    localStorage.removeItem('auth_token')
    localStorage.removeItem('user')
    
    window.location.href = data.data.sign_out_url
  }
  
  return { isAuthenticated, user, signIn, signOut, handleCallback }
}
```

### 登录按钮

```vue
<template>
  <button @click="signIn">使用 Logto 登录</button>
</template>

<script setup>
import { useLogtoAuth } from '@/composables/useLogtoAuth'
const { signIn } = useLogtoAuth()
</script>
```

### 回调页面

```vue
<template>
  <div>Signing in...</div>
</template>

<script setup>
import { onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useLogtoAuth } from '@/composables/useLogtoAuth'

const router = useRouter()
const { handleCallback } = useLogtoAuth()

onMounted(async () => {
  try {
    await handleCallback()
    router.push('/')
  } catch (error) {
    router.push('/login')
  }
})
</script>
```

## 🔧 已创建的配置文件

已为 Logto 集成创建以下文件：

### 后端文件

1. **`config/logto.php`** - Logto 配置
2. **`app/Services/LogtoAuthService.php`** - Logto 认证服务
3. **`app/Http/Controllers/V1/Passport/LogtoAuthController.php`** - API 控制器
4. **`database/migrations/2025_10_29_230700_add_logto_fields_to_users.php`** - 数据库迁移
5. **`app/Models/User.php`** - 已更新 Logto 方法

### 文档

1. **`docs/LOGTO_INTEGRATION.md`** - 综合集成指南
2. **`LOGTO_SETUP.md`** - 本快速设置指南

### 配置

1. **`.env.example`** - 已更新 Logto 环境变量
2. **`composer.json`** - 已添加 `logto/sdk` 依赖

## ✅ 验证清单

- [ ] Composer 依赖已安装
- [ ] 环境变量已配置
- [ ] 数据库迁移已运行
- [ ] Logto 应用程序已创建
- [ ] 重定向 URI 已在 Logto 控制台中配置
- [ ] 凭据已复制到 `.env`
- [ ] 登录端点返回有效 URL
- [ ] 回调端点在数据库中创建用户
- [ ] 用户可以登录并访问受保护的路由

## 🔍 故障排查

### 问题："Invalid redirect URI"（无效的重定向 URI）

**解决方案：** 确保 `.env` 中的重定向 URI 与 Logto 控制台中的完全匹配。

### 问题："User sync failed"（用户同步失败）

**解决方案：** 
1. 检查数据库迁移是否成功运行
2. 验证 `.env` 中的 `LOGTO_AUTO_CREATE_USER=true`
3. 检查 `storage/logs/laravel.log` 中的日志

### 问题："Authentication failed"（认证失败）

**解决方案：**
1. 验证 Logto 凭据是否正确
2. 检查 Logto 端点是否可访问
3. 启用调试模式：`APP_DEBUG=true`
4. 检查日志以获取详细错误消息

## 📚 其他资源

- **完整文档：** `docs/LOGTO_INTEGRATION.md`
- **Logto 文档：** https://docs.logto.io
- **Logto 控制台：** https://cloud.logto.io
- **Xboard GitHub：** https://github.com/ElinksTeam/ElinksBoard

## 🎯 后续步骤

1. **自定义用户同步：** 修改 `LogtoAuthService::createUserFromLogto()` 以设置自定义默认值
2. **添加社交登录：** 在 Logto 控制台中配置社交连接器
3. **启用 MFA：** 在 Logto 中设置多因素认证
4. **配置角色：** 使用 Logto RBAC 进行基于角色的访问控制
5. **更新前端：** 将 Logto 登录集成到您的 Vue3 主题中

## 💡 提示

- **开发：** 使用 Logto Cloud 免费套餐进行测试
- **生产：** 考虑自托管 Logto 以获得完全控制
- **安全：** 生产环境始终使用 HTTPS
- **监控：** 定期检查日志以查找认证问题
- **备份：** 保护好您的 Logto 凭据

## 🆘 需要帮助？

- 检查 `storage/logs/laravel.log` 中的错误
- 查看 Logto 文档：https://docs.logto.io
- 在 GitHub 上提交 issue
- 加入 Logto Discord 社区

---

**集成状态：** ✅ 完成

所有必要的文件已创建。按照上述步骤完成设置。
