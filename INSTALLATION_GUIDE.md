# Xboard Installation Guide with Logto

## 🚀 Quick Installation

### Prerequisites

- PHP 8.2+
- MySQL 5.7+ / PostgreSQL / SQLite
- Redis
- Composer
- Logto account (Cloud or self-hosted)

---

## 📋 Installation Steps

### Step 1: Clone Repository

```bash
git clone https://github.com/ElinksTeam/Xboard.git
cd Xboard
```

### Step 2: Install Dependencies

```bash
composer install
```

### Step 3: Run Installation Command

```bash
php artisan xboard:install
```

### Step 4: Follow Installation Wizard

The installation wizard will guide you through:

#### 4.1 Database Configuration

Choose your database type:
- **SQLite** (Recommended for testing)
- **MySQL**
- **PostgreSQL**

Enter connection details when prompted.

#### 4.2 Redis Configuration

Enter Redis connection details:
- Host (default: 127.0.0.1)
- Port (default: 6379)
- Password (optional)

#### 4.3 Logto Configuration ⭐ **REQUIRED**

You will be prompted to configure Logto:

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

**Where to get these values:**
1. Visit [Logto Console](https://cloud.logto.io) or your self-hosted instance
2. Create a new **Traditional Web Application**
3. Copy the **Endpoint**, **App ID**, and **App Secret**

#### 4.4 Installation Complete

You will see:

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

## 🔧 Post-Installation Configuration

### Step 5: Configure Logto Console

**IMPORTANT:** Before first login, configure Logto Console:

1. Go to your Logto application settings
2. Add **Redirect URI**:
   ```
   http://your-domain.com/api/v1/passport/auth/logto/callback
   ```
3. Add **Post Sign-out Redirect URI**:
   ```
   http://your-domain.com
   ```
4. Save changes

### Step 6: Complete First Login ⚠️ **CRITICAL**

**The first user to login will automatically become the administrator!**

1. Visit your Xboard site: `http://your-domain.com`
2. Click "Sign in with Logto"
3. Complete Logto authentication
4. You will be redirected back with admin privileges

**Security Warning:**
- Do this **immediately** after installation
- Anyone who completes the first login becomes admin
- Subsequent users will be regular users

---

## 🎯 First User Becomes Admin

### How It Works

```
Installation Complete
        ↓
First User Logs In via Logto
        ↓
System Checks: User Count = 0?
        ↓
    YES → Grant Admin (is_admin = 1)
    NO  → Regular User (is_admin = 0)
```

### Admin Privileges

The first user gets:
- ✅ Full admin panel access
- ✅ Logto configuration management
- ✅ User management
- ✅ System settings
- ✅ All administrative features

### Regular Users

Subsequent users get:
- ✅ User dashboard access
- ✅ Service subscription
- ✅ Profile management
- ❌ No admin panel access

---

## 🔐 Security Best Practices

### 1. Complete First Login Immediately

```bash
# Right after installation:
# 1. Configure Logto Console
# 2. Visit your site
# 3. Login with YOUR account
# 4. Verify admin access
```

### 2. Secure Your Logto Account

- Use strong password
- Enable MFA in Logto
- Restrict Logto application access
- Monitor Logto audit logs

### 3. Configure HTTPS

```bash
# Update .env
APP_URL=https://your-domain.com

# Update Logto Console URIs to use HTTPS
```

### 4. Secure Admin Path

The admin path is automatically generated as a hash. Keep it secret:

```
Admin Panel: https://your-domain.com/{random-hash}
```

---

## 🧪 Testing Installation

### Test 1: Check Installation

```bash
# Check if installed
cat .env | grep INSTALLED
# Should show: INSTALLED=true
```

### Test 2: Check Logto Configuration

```bash
# Check Logto settings
cat .env | grep LOGTO
# Should show your Logto configuration
```

### Test 3: Test Sign-in URL

```bash
curl http://your-domain.com/api/v1/passport/auth/logto/sign-in
```

Expected response:
```json
{
  "code": 0,
  "data": {
    "sign_in_url": "https://your-logto.app/oidc/auth?...",
    "message": "Redirect to this URL to sign in with Logto"
  }
}
```

### Test 4: Complete First Login

1. Visit your site
2. Click sign-in
3. Authenticate with Logto
4. Check response includes `"is_admin": true`

---

## 🔄 Troubleshooting

### Issue: "Logto 认证系统未配置"

**Cause:** Logto configuration missing or invalid

**Solution:**
1. Check `.env` file has Logto variables
2. Run `php artisan config:cache`
3. Verify Logto credentials are correct

### Issue: "Invalid redirect URI"

**Cause:** Redirect URI mismatch

**Solution:**
1. Check Logto Console redirect URI matches exactly
2. Ensure no trailing slashes
3. Use correct protocol (http/https)

### Issue: "Connection test failed"

**Cause:** Cannot reach Logto endpoint

**Solution:**
1. Verify Logto endpoint URL is correct
2. Check network connectivity
3. Verify firewall rules
4. Test endpoint in browser

### Issue: "First login didn't grant admin"

**Cause:** Another user logged in first

**Solution:**
1. Check database: `SELECT * FROM v2_user WHERE is_admin = 1;`
2. If wrong user is admin, manually update:
   ```sql
   UPDATE v2_user SET is_admin = 0 WHERE id = {wrong_user_id};
   UPDATE v2_user SET is_admin = 1 WHERE id = {correct_user_id};
   ```

---

## 📊 Verification Checklist

After installation, verify:

- [ ] Installation completed successfully
- [ ] Logto configured in `.env`
- [ ] Logto Console redirect URIs configured
- [ ] First login completed
- [ ] Admin privileges granted
- [ ] Can access admin panel
- [ ] Can modify Logto settings in admin panel
- [ ] Regular users can sign up
- [ ] Regular users don't have admin access

---

## 🎨 Next Steps

### 1. Configure System Settings

Login to admin panel and configure:
- Site name and description
- Email settings
- Payment methods
- Subscription plans

### 2. Customize Logto

In Logto Console:
- Add social login providers (Google, GitHub, etc.)
- Enable MFA
- Customize sign-in page
- Configure password policy

### 3. Deploy Frontend

Update frontend to use Logto authentication:
- See `docs/FRONTEND_LOGTO_INTEGRATION.md`
- Remove traditional login forms
- Add Logto sign-in button
- Implement callback handler

### 4. Test Complete Flow

1. Test user registration via Logto
2. Test user login
3. Test admin panel access
4. Test Logto settings modification
5. Test user permissions

---

## 📚 Additional Resources

- **Quick Setup:** `LOGTO_SETUP.md`
- **Complete Guide:** `docs/LOGTO_INTEGRATION.md`
- **Frontend Guide:** `docs/FRONTEND_LOGTO_INTEGRATION.md`
- **Changes Summary:** `LOGTO_CHANGES.md`
- **Logto Documentation:** https://docs.logto.io

---

## 🆘 Getting Help

If you encounter issues:

1. **Check Logs**
   ```bash
   tail -f storage/logs/laravel.log
   ```

2. **Enable Debug Mode**
   ```env
   APP_DEBUG=true
   LOG_LEVEL=debug
   ```

3. **Verify Configuration**
   ```bash
   php artisan config:show logto
   ```

4. **Test Connection**
   - Login to admin panel
   - Go to Logto settings
   - Click "Test Connection"

5. **Community Support**
   - GitHub Issues
   - Logto Discord
   - Documentation

---

## ⚠️ Important Notes

1. **No Default Admin Account**
   - No default username/password
   - First login creates admin
   - Cannot create admin manually

2. **All Users Use Logto**
   - No traditional login
   - No password management in Xboard
   - All authentication via Logto

3. **Admin Path is Random**
   - Generated during installation
   - Keep it secret
   - Can be changed in settings

4. **First Login is Critical**
   - Determines who becomes admin
   - Cannot be undone easily
   - Complete immediately after installation

---

**Installation Complete!** 🎉

Remember to complete your first login immediately to secure admin access.
