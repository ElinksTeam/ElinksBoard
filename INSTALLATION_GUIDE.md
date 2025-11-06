# Xboard 安装指南（集成 Logto）

## 🚀 快速安装

### 系统要求

- PHP 8.2+
- MySQL 5.7+ / PostgreSQL / SQLite
- Redis
- Composer
- Logto 账号（云端或自托管）

---

## 📋 安装步骤

### 步骤 1：克隆仓库

```bash
git clone https://github.com/ElinksTeam/Xboard.git
cd Xboard
```

### 步骤 2：安装依赖

```bash
composer install
```

### 步骤 3：运行安装命令

```bash
# Installation wizard removed - configure via .env and admin panel
```

### 步骤 4：按照安装向导操作

安装向导将引导您完成以下配置：

#### 4.1 数据库配置

选择数据库类型：
- **SQLite**（推荐用于测试）
- **MySQL**
- **PostgreSQL**

根据提示输入连接详情。

#### 4.2 Redis 配置

输入 Redis 连接详情：
- 主机（默认：127.0.0.1）
- 端口（默认：6379）
- 密码（可选）

#### 4.3 Logto 配置 ⭐ **必需**

系统将提示您配置 Logto：

```
🔐 配置 Logto 认证系统
Logto 是现代化的身份认证服务，支持 SSO、MFA、社交登录等功能

请输入 Logto Endpoint (例如: https://your-tenant.logto.app):
> https://your-tenant.logto.app

请输入 Logto App ID:
> your_app_id_here

请输入 Logto App Secret:
> your_app_secret_here
```

**如何获取这些值：**
1. 访问 [Logto 控制台](https://cloud.logto.io) 或您的自托管实例
2. 创建一个新的 **传统 Web 应用程序**
3. 复制 **Endpoint**、**App ID** 和 **App Secret**

#### 4.4 安装完成

您将看到：

```
🎉：一切就绪

📋 重要信息：

1. 管理面板地址：
   http://your-domain.com/abc123def

2. 首次登录用户将自动成为管理员
   - 使用 Logto 完成首次登录
   - 系统自动授予管理员权限
   - 后续用户为普通用户

3. Logto Console 配置：
   Redirect URI: http://your-domain.com/api/v1/passport/auth/logto/callback
   Post Logout URI: http://your-domain.com

⚠️  安全提示：
请立即完成首次登录以获取管理员权限！
首次登录后，其他用户将只能获得普通用户权限。
```

---

## 🔧 安装后配置

### 步骤 5：配置 Logto 控制台

**重要：** 在首次登录前，配置 Logto 控制台：

1. 进入您的 Logto 应用程序设置
2. 添加 **重定向 URI**：
   ```
   http://your-domain.com/api/v1/passport/auth/logto/callback
   ```
3. 添加 **登出后重定向 URI**：
   ```
   http://your-domain.com
   ```
4. 保存更改

### 步骤 6：完成首次登录 ⚠️ **关键**

**首次登录的用户将自动成为管理员！**

1. 访问您的 Xboard 站点：`http://your-domain.com`
2. 点击"使用 Logto 登录"
3. 完成 Logto 认证
4. 您将被重定向回来并获得管理员权限

**安全警告：**
- 安装后**立即**完成此操作
- 任何完成首次登录的人都将成为管理员
- 后续用户将是普通用户

---

## 🎯 首次用户成为管理员

### 工作原理

```
安装完成
    ↓
首次用户通过 Logto 登录
    ↓
系统检查：用户数 = 0？
    ↓
是 → 授予管理员权限 (is_admin = 1)
否 → 普通用户 (is_admin = 0)
```

### 管理员权限

首次用户获得：
- ✅ 完整的管理面板访问权限
- ✅ Logto 配置管理
- ✅ 用户管理
- ✅ 系统设置
- ✅ 所有管理功能

### 普通用户

后续用户获得：
- ✅ 用户仪表板访问权限
- ✅ 服务订阅
- ✅ 个人资料管理
- ❌ 无管理面板访问权限

---

## 🔐 安全最佳实践

### 1. 立即完成首次登录

```bash
# 安装后立即：
# 1. 配置 Logto 控制台
# 2. 访问您的站点
# 3. 使用您的账号登录
# 4. 验证管理员访问权限
```

### 2. 保护您的 Logto 账号

- 使用强密码
- 在 Logto 中启用 MFA
- 限制 Logto 应用程序访问
- 监控 Logto 审计日志

### 3. 配置 HTTPS

```bash
# 更新 .env
APP_URL=https://your-domain.com

# 更新 Logto 控制台 URI 使用 HTTPS
```

### 4. 保护管理员路径

管理员路径自动生成为哈希值。请保密：

```
管理面板：https://your-domain.com/{随机哈希}
```

---

## 🧪 测试安装

### 测试 1：检查安装状态

```bash
# 检查是否已安装
cat .env | grep INSTALLED
# 应显示：INSTALLED=true
```

### 测试 2：检查 Logto 配置

```bash
# 检查 Logto 设置
cat .env | grep LOGTO
# 应显示您的 Logto 配置
```

### 测试 3：测试登录 URL

```bash
curl http://your-domain.com/api/v1/passport/auth/logto/sign-in
```

预期响应：
```json
{
  "code": 0,
  "data": {
    "sign_in_url": "https://your-logto.app/oidc/auth?...",
    "message": "Redirect to this URL to sign in with Logto"
  }
}
```

### 测试 4：完成首次登录

1. 访问您的站点
2. 点击登录
3. 使用 Logto 认证
4. 检查响应包含 `"is_admin": true`

---

## 🔄 故障排查

### 问题："Logto 认证系统未配置"

**原因：** Logto 配置缺失或无效

**解决方案：**
1. 检查 `.env` 文件是否包含 Logto 变量
2. 运行 `php artisan config:cache`
3. 验证 Logto 凭据是否正确

### 问题："Invalid redirect URI"（无效的重定向 URI）

**原因：** 重定向 URI 不匹配

**解决方案：**
1. 检查 Logto 控制台中的重定向 URI 是否完全匹配
2. 确保没有尾部斜杠
3. 使用正确的协议（http/https）

### 问题："Connection test failed"（连接测试失败）

**原因：** 无法访问 Logto 端点

**解决方案：**
1. 验证 Logto 端点 URL 是否正确
2. 检查网络连接
3. 验证防火墙规则
4. 在浏览器中测试端点

### 问题："首次登录未授予管理员权限"

**原因：** 其他用户先登录了

**解决方案：**
1. 检查数据库：`SELECT * FROM v2_user WHERE is_admin = 1;`
2. 如果错误的用户是管理员，手动更新：
   ```sql
   UPDATE v2_user SET is_admin = 0 WHERE id = {错误用户ID};
   UPDATE v2_user SET is_admin = 1 WHERE id = {正确用户ID};
   ```

---

## 📊 验证清单

安装后，请验证：

- [ ] 安装成功完成
- [ ] Logto 已在 `.env` 中配置
- [ ] Logto 控制台重定向 URI 已配置
- [ ] 首次登录已完成
- [ ] 管理员权限已授予
- [ ] 可以访问管理面板
- [ ] 可以在管理面板中修改 Logto 设置
- [ ] 普通用户可以注册
- [ ] 普通用户没有管理员访问权限

---

## 🎨 后续步骤

### 1. 配置系统设置

登录管理面板并配置：
- 站点名称和描述
- 邮件设置
- 支付方式
- 订阅计划

### 2. 自定义 Logto

在 Logto 控制台中：
- 添加社交登录提供商（Google、GitHub 等）
- 启用 MFA
- 自定义登录页面
- 配置密码策略

### 3. 部署前端

更新前端以使用 Logto 认证：
- 参见 `docs/FRONTEND_LOGTO_INTEGRATION.md`
- 移除传统登录表单
- 添加 Logto 登录按钮
- 实现回调处理程序

### 4. 测试完整流程

1. 测试通过 Logto 注册用户
2. 测试用户登录
3. 测试管理面板访问
4. 测试 Logto 设置修改
5. 测试用户权限

---

## 📚 其他资源

- **快速设置：** `LOGTO_SETUP.md`
- **完整指南：** `docs/LOGTO_INTEGRATION.md`
- **前端指南：** `docs/FRONTEND_LOGTO_INTEGRATION.md`
- **变更摘要：** `LOGTO_CHANGES.md`
- **Logto 文档：** https://docs.logto.io

---

## 🆘 获取帮助

如果遇到问题：

1. **检查日志**
   ```bash
   tail -f storage/logs/laravel.log
   ```

2. **启用调试模式**
   ```env
   APP_DEBUG=true
   LOG_LEVEL=debug
   ```

3. **验证配置**
   ```bash
   php artisan config:show logto
   ```

4. **测试连接**
   - 登录管理面板
   - 进入 Logto 设置
   - 点击"测试连接"

5. **社区支持**
   - GitHub Issues
   - Logto Discord
   - 文档

---

## ⚠️ 重要说明

1. **没有默认管理员账号**
   - 没有默认用户名/密码
   - 首次登录创建管理员
   - 无法手动创建管理员

2. **所有用户使用 Logto**
   - 没有传统登录
   - Xboard 中没有密码管理
   - 所有认证通过 Logto

3. **管理员路径是随机的**
   - 安装时生成
   - 请保密
   - 可在设置中更改

4. **首次登录至关重要**
   - 决定谁成为管理员
   - 不易撤销
   - 安装后立即完成

---

**安装完成！** 🎉

记得立即完成首次登录以确保管理员访问权限。
