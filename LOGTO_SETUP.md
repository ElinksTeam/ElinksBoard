# Logto Integration - Quick Setup Guide

## ⚠️ Important Changes

**This version removes traditional email/password login and uses Logto exclusively for ALL user authentication.**

- ✅ Logto configuration is **required** during installation
- ✅ **First user to login becomes administrator automatically**
- ✅ Admin can modify Logto settings in backend panel
- ✅ Traditional login routes have been removed
- ✅ All users (including admin) authenticate through Logto
- ⚠️ **Security: Complete first login immediately after installation!**

## 🚀 Quick Start (5 Minutes)

### Step 1: Install Dependencies

```bash
composer install
```

The Logto SDK (`logto/sdk`) has already been added to `composer.json`.

### Step 2: Configure Environment

Copy `.env.example` to `.env` (if not already done) and add:

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

### Step 3: Run Database Migration

```bash
php artisan migrate
```

This adds `logto_sub` and `auth_provider` fields to the users table.

### Step 4: Configure Logto Console

1. **Create Application**
   - Go to [Logto Console](https://cloud.logto.io) (or your self-hosted instance)
   - Click **Applications** → **Create application**
   - Select **Traditional Web Application**
   - Choose **PHP** framework

2. **Configure Redirect URIs**
   
   Add these URIs in your application settings:
   
   **Redirect URIs:**
   ```
   http://localhost:3000/api/v1/passport/auth/logto/callback
   https://your-domain.com/api/v1/passport/auth/logto/callback
   ```
   
   **Post Sign-out Redirect URIs:**
   ```
   http://localhost:3000
   https://your-domain.com
   ```

3. **Copy Credentials**
   
   From the application details page, copy:
   - **App ID** → Update `LOGTO_APP_ID` in `.env`
   - **App Secret** → Update `LOGTO_APP_SECRET` in `.env`
   - **Endpoint** → Update `LOGTO_ENDPOINT` in `.env`

### Step 5: Complete First Login (Critical!)

**⚠️ IMPORTANT: The first user to login will automatically become the administrator!**

1. Immediately after installation, visit your site
2. Click "Sign in with Logto"
3. Complete the Logto authentication
4. You will be granted administrator privileges automatically
5. Subsequent users will be regular users

**Security Note:** Do not delay this step! Anyone who completes the first login will become admin.

### Step 6: Test the Integration

#### Option A: Using cURL

```bash
# Get sign-in URL
curl http://localhost/api/v1/passport/auth/logto/sign-in

# Response will contain sign_in_url - open it in browser
```

#### Option B: Using Browser

1. Visit: `http://localhost/api/v1/passport/auth/logto/sign-in`
2. Copy the `sign_in_url` from the response
3. Open the URL in your browser
4. Complete the Logto sign-in process
5. You'll be redirected back with authentication data

## 📋 Available API Endpoints

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/api/v1/passport/auth/logto/sign-in` | Get Logto sign-in URL |
| GET | `/api/v1/passport/auth/logto/callback` | Handle OIDC callback |
| POST | `/api/v1/passport/auth/logto/sign-out` | Sign out and get sign-out URL |
| GET | `/api/v1/passport/auth/logto/userinfo` | Get current user info |
| GET | `/api/v1/passport/auth/logto/check` | Check authentication status |

## 🎨 Frontend Integration

### Vue 3 Example

Create a composable for Logto authentication:

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

### Login Button

```vue
<template>
  <button @click="signIn">Sign in with Logto</button>
</template>

<script setup>
import { useLogtoAuth } from '@/composables/useLogtoAuth'
const { signIn } = useLogtoAuth()
</script>
```

### Callback Page

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

## 🔧 Configuration Files Created

The following files have been created for Logto integration:

### Backend Files

1. **`config/logto.php`** - Logto configuration
2. **`app/Services/LogtoAuthService.php`** - Logto authentication service
3. **`app/Http/Controllers/V1/Passport/LogtoAuthController.php`** - API controller
4. **`database/migrations/2025_10_29_230700_add_logto_fields_to_users.php`** - Database migration
5. **`app/Models/User.php`** - Updated with Logto methods

### Documentation

1. **`docs/LOGTO_INTEGRATION.md`** - Comprehensive integration guide
2. **`LOGTO_SETUP.md`** - This quick setup guide

### Configuration

1. **`.env.example`** - Updated with Logto environment variables
2. **`composer.json`** - Added `logto/sdk` dependency

## ✅ Verification Checklist

- [ ] Composer dependencies installed
- [ ] Environment variables configured
- [ ] Database migration run
- [ ] Logto application created
- [ ] Redirect URIs configured in Logto Console
- [ ] Credentials copied to `.env`
- [ ] Sign-in endpoint returns valid URL
- [ ] Callback endpoint creates user in database
- [ ] User can sign in and access protected routes

## 🔍 Troubleshooting

### Issue: "Invalid redirect URI"

**Solution:** Ensure the redirect URI in `.env` exactly matches the one in Logto Console.

### Issue: "User sync failed"

**Solution:** 
1. Check database migration ran successfully
2. Verify `LOGTO_AUTO_CREATE_USER=true` in `.env`
3. Check logs in `storage/logs/laravel.log`

### Issue: "Authentication failed"

**Solution:**
1. Verify Logto credentials are correct
2. Check Logto endpoint is accessible
3. Enable debug mode: `APP_DEBUG=true`
4. Check logs for detailed error messages

## 📚 Additional Resources

- **Full Documentation:** `docs/LOGTO_INTEGRATION.md`
- **Logto Docs:** https://docs.logto.io
- **Logto Console:** https://cloud.logto.io
- **Xboard GitHub:** https://github.com/cedar2025/Xboard

## 🎯 Next Steps

1. **Customize User Sync:** Modify `LogtoAuthService::createUserFromLogto()` to set custom default values
2. **Add Social Logins:** Configure social connectors in Logto Console
3. **Enable MFA:** Set up multi-factor authentication in Logto
4. **Configure Roles:** Use Logto RBAC for role-based access control
5. **Update Frontend:** Integrate Logto sign-in into your Vue3 theme

## 💡 Tips

- **Development:** Use Logto Cloud free tier for testing
- **Production:** Consider self-hosting Logto for full control
- **Security:** Always use HTTPS in production
- **Monitoring:** Check logs regularly for authentication issues
- **Backup:** Keep your Logto credentials secure

## 🆘 Need Help?

- Check `storage/logs/laravel.log` for errors
- Review Logto documentation: https://docs.logto.io
- Open an issue on GitHub
- Join Logto Discord community

---

**Integration Status:** ✅ Complete

All necessary files have been created. Follow the steps above to complete the setup.
