# Admin Panel Logto Integration Guide

## 概述

ElinksBoard Admin Panel 現已完全整合 Logto 認證系統，支援基於角色的存取控制（RBAC）。管理員可以透過 Logto 登入，權限由 Logto 角色管理。

## 功能特性

### ✅ 已實現

- **混合認證模式** - 支援 Logto 角色和本地 `is_admin` 標記
- **自動角色同步** - 登入時自動從 Logto 同步使用者角色
- **管理員權限檢查** - Middleware 自動檢查 Logto admin 角色
- **角色管理介面** - 管理後台可查看和管理使用者角色
- **向後相容** - 現有本地管理員帳號繼續有效

### 🎯 權限優先級

1. **Logto 角色**（優先）- 如果使用者透過 Logto 認證且有 `admin` 角色
2. **本地 is_admin**（回退）- 如果使用者有本地管理員標記

## 設定步驟

### 1. 在 Logto Console 創建角色

```
Logto Console → Roles → Create Role

Name: admin
Description: Administrator role with full access to admin panel
```

### 2. 分配角色給使用者

```
Logto Console → Users → Select User → Roles → Assign Role

選擇 "admin" 角色
```

### 3. 更新 Logto 應用程式設定

確保您的 Logto 應用程式包含 `roles` scope：

```
Logto Console → Applications → Your App → Permissions

Scopes:
  ✅ openid
  ✅ profile
  ✅ email
  ✅ phone
  ✅ offline_access
  ✅ roles  ← 確保已啟用
```

### 4. 執行資料庫遷移

```bash
# 如果使用 Docker
docker compose exec web php artisan migrate

# 如果是本地環境
php artisan migrate
```

這會添加以下欄位到 `v2_user` 表：
- `logto_roles` (JSON) - 儲存使用者的 Logto 角色
- `logto_organizations` (JSON) - 儲存使用者的組織（未來使用）
- `logto_roles_synced_at` (TIMESTAMP) - 角色最後同步時間

### 5. 測試管理員登入

1. 造訪您的網站
2. 點選「使用 Logto 登入」
3. 使用已分配 `admin` 角色的 Logto 帳號登入
4. 登入後應該可以存取管理後台

## API 端點

### 查看具有角色的使用者

```http
GET /api/v2/admin/logto/users-with-roles

Response:
{
  "data": [
    {
      "id": 1,
      "email": "admin@example.com",
      "logto_sub": "user_abc123",
      "logto_roles": ["admin"],
      "logto_roles_synced_at": "2025-11-06T19:00:00Z",
      "is_admin": true,
      "last_login_at": 1699286400
    }
  ],
  "meta": {
    "current_page": 1,
    "per_page": 20,
    "total": 1
  }
}
```

### 查看 Logto 統計

```http
GET /api/v2/admin/logto/stats

Response:
{
  "data": {
    "total_users": 100,
    "logto_users": 80,
    "local_users": 20,
    "logto_admins": 5,
    "logto_percentage": 80.00
  }
}
```

### 手動同步使用者角色

```http
POST /api/v2/admin/logto/sync-user-roles
Content-Type: application/json

{
  "user_id": 1
}

Response:
{
  "data": {
    "message": "Roles will be synced on next user login",
    "current_roles": ["admin"],
    "last_synced": "2025-11-06T19:00:00Z"
  }
}
```

## 程式碼範例

### 檢查使用者是否有管理員權限

```php
use App\Models\User;

$user = User::find(1);

// 方法 1: 使用模型方法（推薦）
if ($user->hasAdminAccess()) {
    // 使用者有管理員權限
}

// 方法 2: 檢查特定 Logto 角色
if ($user->hasLogtoRole('admin')) {
    // 使用者有 admin 角色
}

// 方法 3: 取得所有角色
$roles = $user->getLogtoRoles();
// ['admin', 'moderator']
```

### 在 Controller 中使用

```php
use Illuminate\Support\Facades\Auth;

class MyController extends Controller
{
    public function adminOnly()
    {
        $user = Auth::user();
        
        if (!$user->hasAdminAccess()) {
            return response()->json(['message' => 'Forbidden'], 403);
        }
        
        // 管理員邏輯
    }
}
```

### 在 Middleware 中使用

Admin middleware 已自動處理 Logto 角色檢查：

```php
// app/Http/Middleware/Admin.php
protected function checkAdminAccess(User $user): bool
{
    // 優先檢查 Logto 角色
    if ($user->auth_provider === 'logto' && $user->logto_roles) {
        if (in_array('admin', $user->logto_roles)) {
            return true;
        }
    }
    
    // 回退到本地 is_admin
    return (bool) $user->is_admin;
}
```

## 角色同步機制

### 自動同步

角色會在以下情況自動同步：

1. **使用者登入時** - 每次透過 Logto 登入都會同步角色
2. **Token 刷新時** - 如果實現了 token 刷新機制

### 同步流程

```
1. 使用者透過 Logto 登入
   ↓
2. LogtoAuthService 獲取 ID Token
   ↓
3. 從 ID Token Claims 提取 roles
   ↓
4. 更新 User 模型的 logto_roles 欄位
   ↓
5. 設定 logto_roles_synced_at 時間戳
   ↓
6. Admin Middleware 檢查角色
```

### 角色資料結構

```json
{
  "logto_roles": ["admin", "moderator"],
  "logto_organizations": ["org_abc123"],
  "logto_roles_synced_at": "2025-11-06T19:00:00.000000Z"
}
```

## 疑難排解

### 問題：使用者有 admin 角色但無法存取管理後台

**檢查清單：**

1. 確認 Logto 應用程式已啟用 `roles` scope
2. 檢查使用者的 `logto_roles` 欄位：
   ```sql
   SELECT id, email, logto_roles, logto_roles_synced_at 
   FROM v2_user 
   WHERE email = 'user@example.com';
   ```
3. 檢查角色同步時間是否最新
4. 嘗試重新登入以觸發角色同步

### 問題：角色未同步

**解決方案：**

1. 檢查 Logto ID Token Claims：
   ```php
   $logtoService = app(LogtoAuthService::class);
   $claims = $logtoService->getIdTokenClaims();
   dd($claims->roles);
   ```

2. 檢查日誌：
   ```bash
   tail -f storage/logs/laravel.log | grep "Synced user roles"
   ```

3. 手動觸發同步：
   ```bash
   # 使用者需要重新登入
   ```

### 問題：本地管理員無法登入

**說明：**

本地管理員（`is_admin = 1`）仍然可以使用傳統方式登入。Logto 整合不影響現有本地帳號。

## 安全建議

### 1. 使用 Logto 角色作為主要權限來源

對於新使用者，建議完全依賴 Logto 角色：

```php
// 在 Logto Console 管理角色
// 不要手動設定 is_admin = 1
```

### 2. 定期審查管理員權限

```sql
-- 查看所有管理員
SELECT id, email, auth_provider, is_admin, logto_roles
FROM v2_user
WHERE is_admin = 1 OR JSON_CONTAINS(logto_roles, '"admin"');
```

### 3. 啟用 MFA

在 Logto Console 為管理員帳號啟用多因素認證：

```
Logto Console → Users → Select Admin User → Security → Enable MFA
```

### 4. 監控管理員活動

```php
// 在 Admin Middleware 中記錄
Log::info('Admin access', [
    'user_id' => $user->id,
    'email' => $user->email,
    'ip' => $request->ip(),
    'path' => $request->path(),
]);
```

## 遷移指南

### 從本地管理員遷移到 Logto

1. **為現有管理員創建 Logto 帳號**
   ```
   Logto Console → Users → Create User
   使用相同的 email
   ```

2. **分配 admin 角色**
   ```
   Logto Console → Users → Select User → Roles → Assign "admin"
   ```

3. **測試登入**
   - 使用 Logto 登入
   - 確認可以存取管理後台

4. **（可選）移除本地 is_admin 標記**
   ```sql
   UPDATE v2_user 
   SET is_admin = 0 
   WHERE email = 'admin@example.com' 
   AND auth_provider = 'logto';
   ```

## 進階配置

### 自訂角色名稱

如果您的 Logto 使用不同的角色名稱：

```php
// app/Http/Middleware/Admin.php
protected function checkAdminAccess(User $user): bool
{
    if ($user->auth_provider === 'logto' && $user->logto_roles) {
        // 檢查多個可能的管理員角色
        $adminRoles = ['admin', 'administrator', 'super_admin'];
        $hasAdminRole = !empty(array_intersect($adminRoles, $user->logto_roles));
        
        if ($hasAdminRole) {
            return true;
        }
    }
    
    return (bool) $user->is_admin;
}
```

### 組織層級權限（未來功能）

```php
// 檢查使用者是否屬於特定組織
if ($user->belongsToOrganization('org_abc123')) {
    // 組織特定邏輯
}
```

## 相關文件

- [Logto 快速設定](../LOGTO_SETUP.md)
- [Logto 變更說明](../LOGTO_CHANGES.md)
- [前端 Logto 整合](FRONTEND_LOGTO_INTEGRATION.md)
- [Logto 官方文件](https://docs.logto.io)

## 支援

如有問題，請：

1. 查看日誌：`storage/logs/laravel.log`
2. 檢查 Logto Console 設定
3. 提交 Issue：https://github.com/ElinksTeam/ElinksBoard/issues
