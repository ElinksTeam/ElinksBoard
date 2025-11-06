# Admin Panel Logto Integration - Testing Guide

## 測試前準備

### 1. 執行資料庫遷移

```bash
# Docker 環境
docker compose exec web php artisan migrate

# 本地環境
php artisan migrate
```

### 2. 在 Logto Console 設定

1. **創建 admin 角色**
   - 前往 Logto Console → Roles
   - 創建名為 `admin` 的角色
   - 描述：Administrator role with full access

2. **確認應用程式 Scopes**
   - 前往 Applications → Your App → Permissions
   - 確保包含：`openid`, `profile`, `email`, `roles`

3. **創建測試使用者**
   - 前往 Users → Create User
   - Email: `admin-test@example.com`
   - 分配 `admin` 角色

## 測試場景

### 場景 1：新使用者透過 Logto 登入（有 admin 角色）

**步驟：**
1. 造訪網站首頁
2. 點選「使用 Logto 登入」
3. 使用有 `admin` 角色的 Logto 帳號登入
4. 完成認證後返回網站

**預期結果：**
- ✅ 使用者成功登入
- ✅ 可以存取管理後台
- ✅ 資料庫中 `logto_roles` 欄位包含 `["admin"]`
- ✅ `logto_roles_synced_at` 有時間戳
- ✅ 日誌顯示：`Synced user roles from Logto`

**驗證：**
```sql
SELECT id, email, auth_provider, is_admin, logto_roles, logto_roles_synced_at
FROM v2_user
WHERE email = 'admin-test@example.com';
```

### 場景 2：新使用者透過 Logto 登入（無 admin 角色）

**步驟：**
1. 在 Logto 創建新使用者（不分配 admin 角色）
2. 使用該帳號登入

**預期結果：**
- ✅ 使用者成功登入
- ❌ 無法存取管理後台（403 Forbidden）
- ✅ `logto_roles` 為空陣列 `[]`
- ✅ 日誌顯示：`Admin access denied`

### 場景 3：現有本地管理員登入

**步驟：**
1. 使用現有的本地管理員帳號登入（`is_admin = 1`）

**預期結果：**
- ✅ 可以正常登入
- ✅ 可以存取管理後台
- ✅ 不受 Logto 整合影響

### 場景 4：角色更新同步

**步驟：**
1. 使用者以普通使用者身份登入（無 admin 角色）
2. 在 Logto Console 為該使用者分配 admin 角色
3. 使用者登出後重新登入

**預期結果：**
- ✅ 重新登入後可以存取管理後台
- ✅ `logto_roles` 更新為 `["admin"]`
- ✅ `logto_roles_synced_at` 更新為最新時間

### 場景 5：角色移除同步

**步驟：**
1. 使用者以管理員身份登入（有 admin 角色）
2. 在 Logto Console 移除該使用者的 admin 角色
3. 使用者登出後重新登入

**預期結果：**
- ✅ 重新登入後無法存取管理後台
- ✅ `logto_roles` 更新為 `[]`
- ✅ 收到 403 Forbidden 錯誤

### 場景 6：API 端點測試

**測試 1：查看具有角色的使用者**
```bash
curl -X GET "http://your-domain.com/api/v2/admin/logto/users-with-roles" \
  -H "Authorization: Bearer YOUR_ADMIN_TOKEN"
```

**預期結果：**
```json
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
  ]
}
```

**測試 2：查看統計**
```bash
curl -X GET "http://your-domain.com/api/v2/admin/logto/stats" \
  -H "Authorization: Bearer YOUR_ADMIN_TOKEN"
```

**預期結果：**
```json
{
  "data": {
    "total_users": 10,
    "logto_users": 8,
    "local_users": 2,
    "logto_admins": 3,
    "logto_percentage": 80.00
  }
}
```

## 日誌檢查

### 成功登入的日誌

```
[2025-11-06 19:00:00] local.INFO: User authenticated via Logto
{
  "user_id": 1,
  "email": "admin@example.com",
  "logto_sub": "user_abc123",
  "is_admin": true,
  "is_first_user": false
}

[2025-11-06 19:00:00] local.INFO: Synced user roles from Logto
{
  "user_id": 1,
  "roles": ["admin"]
}
```

### 管理員存取的日誌

```
[2025-11-06 19:00:01] local.DEBUG: Admin access granted via Logto role
{
  "user_id": 1,
  "roles": ["admin"]
}
```

### 存取被拒的日誌

```
[2025-11-06 19:00:01] local.WARNING: Admin access denied
{
  "user_id": 2,
  "email": "user@example.com",
  "is_admin": false,
  "logto_roles": []
}
```

## 常見問題排查

### 問題 1：角色未同步

**檢查：**
```bash
# 查看日誌
docker compose logs -f web | grep "logto\|role"

# 檢查資料庫
docker compose exec web php artisan tinker
>>> $user = User::where('email', 'admin@example.com')->first();
>>> $user->logto_roles;
>>> $user->logto_roles_synced_at;
```

**可能原因：**
- Logto 應用程式未啟用 `roles` scope
- ID Token 中沒有 roles claim
- 角色同步邏輯有錯誤

### 問題 2：有角色但無法存取管理後台

**檢查：**
```php
// 在 tinker 中測試
$user = User::find(1);
$user->hasAdminAccess();  // 應該返回 true
$user->hasLogtoRole('admin');  // 應該返回 true
$user->getLogtoRoles();  // 應該返回 ['admin']
```

**可能原因：**
- Middleware 邏輯錯誤
- 角色名稱不匹配（檢查是否為 'admin'）
- 快取問題

### 問題 3：本地管理員無法登入

**檢查：**
```sql
SELECT id, email, is_admin, auth_provider
FROM v2_user
WHERE is_admin = 1;
```

**解決：**
- 確認 `is_admin = 1`
- 確認 Middleware 有回退邏輯
- 檢查 Sanctum token 是否有效

## 效能測試

### 測試角色檢查效能

```php
// 測試 1000 次角色檢查
$user = User::find(1);
$start = microtime(true);

for ($i = 0; $i < 1000; $i++) {
    $user->hasAdminAccess();
}

$end = microtime(true);
echo "Time: " . ($end - $start) . " seconds\n";
// 預期：< 0.1 秒
```

### 測試資料庫查詢

```sql
-- 應該使用索引
EXPLAIN SELECT * FROM v2_user WHERE auth_provider = 'logto';

-- 檢查 JSON 查詢效能
EXPLAIN SELECT * FROM v2_user 
WHERE JSON_CONTAINS(logto_roles, '"admin"');
```

## 安全測試

### 測試 1：未授權存取

```bash
# 嘗試不帶 token 存取管理端點
curl -X GET "http://your-domain.com/api/v2/admin/logto/stats"

# 預期：401 Unauthorized
```

### 測試 2：非管理員存取

```bash
# 使用普通使用者 token 存取管理端點
curl -X GET "http://your-domain.com/api/v2/admin/logto/stats" \
  -H "Authorization: Bearer USER_TOKEN"

# 預期：403 Forbidden
```

### 測試 3：角色注入攻擊

```bash
# 嘗試手動設定角色
curl -X POST "http://your-domain.com/api/v2/user/profile" \
  -H "Authorization: Bearer USER_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"logto_roles": ["admin"]}'

# 預期：應該被拒絕或忽略
```

## 回歸測試清單

- [ ] 現有本地管理員可以正常登入
- [ ] 現有本地使用者可以正常登入
- [ ] Logto 使用者可以正常登入
- [ ] 管理後台所有功能正常運作
- [ ] 使用者前端所有功能正常運作
- [ ] API 端點正常回應
- [ ] 權限檢查正確執行
- [ ] 日誌記錄正常
- [ ] 效能無明顯下降

## 測試完成檢查表

- [ ] 資料庫遷移成功執行
- [ ] Logto Console 正確設定
- [ ] 所有測試場景通過
- [ ] 日誌輸出正確
- [ ] API 端點正常運作
- [ ] 安全測試通過
- [ ] 效能測試通過
- [ ] 回歸測試通過
- [ ] 文件已更新
- [ ] 團隊成員已培訓

## 下一步

測試完成後：

1. **部署到測試環境**
   - 執行完整測試套件
   - 邀請團隊成員測試

2. **監控**
   - 設定日誌監控
   - 追蹤錯誤率
   - 監控效能指標

3. **文件**
   - 更新使用者手冊
   - 更新管理員指南
   - 記錄已知問題

4. **部署到生產環境**
   - 備份資料庫
   - 執行遷移
   - 監控系統狀態
