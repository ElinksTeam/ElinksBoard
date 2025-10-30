# Logto Integration Guide

This document describes how to integrate Logto authentication into Xboard.

## Overview

Xboard now supports Logto as an authentication provider, offering:
- OAuth 2.0 + OpenID Connect (OIDC) authentication
- Single Sign-On (SSO)
- Multi-Factor Authentication (MFA)
- Social login providers
- Enterprise SSO (SAML)

## Architecture

```
┌─────────────┐         ┌──────────────┐         ┌─────────────┐
│   Frontend  │────────▶│    Logto     │────────▶│   Xboard    │
│   (Vue3)    │  OIDC   │  Auth Server │  Token  │   Backend   │
└─────────────┘         └──────────────┘         └─────────────┘
                              │                         │
                              │                         │
                              ▼                         ▼
                        User Authentication       Business Data
```

## Installation

### 1. Install Logto SDK

The Logto SDK has been added to `composer.json`:

```bash
composer install
```

### 2. Configure Environment Variables

Add the following to your `.env` file:

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

### 3. Run Database Migrations

```bash
php artisan migrate
```

This will add the following fields to the `v2_user` table:
- `logto_sub`: Logto user ID (unique identifier)
- `auth_provider`: Authentication provider ('local' or 'logto')

## Logto Console Configuration

### 1. Create Application

1. Go to [Logto Console](https://cloud.logto.io) or your self-hosted instance
2. Navigate to **Applications** → **Create application**
3. Select **Traditional Web Application**
4. Choose **PHP** as the framework

### 2. Configure Redirect URIs

Add the following URIs in your application settings:

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

### 3. Copy Credentials

Copy the following from your Logto application:
- **App ID** → `LOGTO_APP_ID`
- **App Secret** → `LOGTO_APP_SECRET`
- **Endpoint** → `LOGTO_ENDPOINT`

## API Endpoints

### Authentication Flow

#### 1. Initiate Sign-In

**GET** `/api/v1/passport/auth/logto/sign-in`

Returns the Logto sign-in URL.

**Response:**
```json
{
  "code": 0,
  "data": {
    "sign_in_url": "https://your-logto.app/oidc/auth?...",
    "message": "Redirect to this URL to sign in with Logto"
  }
}
```

**Frontend Usage:**
```javascript
const response = await fetch('/api/v1/passport/auth/logto/sign-in');
const { data } = await response.json();
window.location.href = data.sign_in_url;
```

#### 2. Handle Callback

**GET** `/api/v1/passport/auth/logto/callback`

Processes the OIDC callback and returns user data with authentication token.

**Query Parameters:**
- `code`: Authorization code from Logto
- `state`: State parameter for CSRF protection

**Response:**
```json
{
  "code": 0,
  "data": {
    "user": {
      "id": 1,
      "email": "user@example.com",
      "uuid": "...",
      "is_admin": false,
      "balance": 0,
      "transfer_enable": 0
    },
    "auth_data": "Bearer eyJ0eXAiOiJKV1QiLCJhbGc...",
    "token": "abc123...",
    "is_admin": false,
    "message": "Authentication successful"
  }
}
```

#### 3. Sign Out

**POST** `/api/v1/passport/auth/logto/sign-out`

Returns the Logto sign-out URL and clears local tokens.

**Response:**
```json
{
  "code": 0,
  "data": {
    "sign_out_url": "https://your-logto.app/oidc/session/end?...",
    "message": "Redirect to this URL to complete sign-out"
  }
}
```

#### 4. Get User Info

**GET** `/api/v1/passport/auth/logto/userinfo`

Returns current user information from both local database and Logto.

**Response:**
```json
{
  "code": 0,
  "data": {
    "user": {
      "id": 1,
      "email": "user@example.com",
      "balance": 0
    },
    "logto_user": {
      "sub": "logto_user_id",
      "email": "user@example.com",
      "name": "John Doe",
      "picture": "https://..."
    }
  }
}
```

#### 5. Check Authentication Status

**GET** `/api/v1/passport/auth/logto/check`

Checks if the user is authenticated with Logto.

**Response:**
```json
{
  "code": 0,
  "data": {
    "is_authenticated": true,
    "user_id": 1,
    "email": "user@example.com",
    "is_admin": false
  }
}
```

## Frontend Integration

### Vue 3 Example

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
  
  return {
    isAuthenticated,
    user,
    signIn,
    signOut,
    handleCallback
  }
}
```

### Login Page

```vue
<template>
  <div class="login-page">
    <h1>Welcome to Xboard</h1>
    <button @click="handleLogin" class="btn-primary">
      Sign in with Logto
    </button>
  </div>
</template>

<script setup lang="ts">
import { useLogtoAuth } from '@/composables/useLogtoAuth'

const { signIn } = useLogtoAuth()

function handleLogin() {
  signIn()
}
</script>
```

### Callback Page

```vue
<template>
  <div class="callback-page">
    <p>Signing in...</p>
  </div>
</template>

<script setup lang="ts">
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
    console.error('Authentication failed', error)
    router.push('/login')
  }
})
</script>
```

## User Synchronization

### Auto-Create Users

When `LOGTO_AUTO_CREATE_USER=true`, new users are automatically created in the local database on first login.

Default user attributes:
```php
[
    'transfer_enable' => 0,
    'u' => 0,
    'd' => 0,
    'balance' => 0,
    'commission_balance' => 0,
    'expired_at' => null,
]
```

### Auto-Update Users

When `LOGTO_AUTO_UPDATE_USER=true`, user information is updated from Logto on each login:
- Email
- Name
- Username
- Avatar (picture)
- Phone number

### User Linking

If a user with the same email exists in the local database (from traditional auth), they will be automatically linked to the Logto account on first Logto login.

## Database Schema

### New Fields in `v2_user` Table

| Field | Type | Description |
|-------|------|-------------|
| `logto_sub` | VARCHAR(255) | Logto user ID (unique) |
| `auth_provider` | VARCHAR(20) | Authentication provider: 'local' or 'logto' |

### Indexes

- `idx_logto_sub` on `logto_sub`
- `idx_auth_provider` on `auth_provider`

## Security Considerations

### Token Management

- Logto tokens are stored in PHP session (configurable)
- Sanctum tokens are generated for API access
- Both token types are cleared on sign-out

### Password Handling

- Logto users have `password = NULL`
- Password management is handled by Logto
- Local password authentication is still available for backward compatibility

### CSRF Protection

- Logto uses `state` parameter for CSRF protection
- Laravel CSRF middleware is applied to sign-out endpoint

## Troubleshooting

### Common Issues

#### 1. "Invalid redirect URI"

**Solution:** Ensure the redirect URI in `.env` matches exactly with the one configured in Logto Console.

#### 2. "User sync failed"

**Solution:** Check that:
- Database migration has been run
- `LOGTO_AUTO_CREATE_USER` is set to `true`
- User table has `logto_sub` and `auth_provider` columns

#### 3. "Authentication failed"

**Solution:** Verify:
- Logto credentials are correct
- Logto endpoint is accessible
- PHP session is working properly

### Debug Mode

Enable debug logging in `.env`:

```env
APP_DEBUG=true
LOG_LEVEL=debug
```

Check logs in `storage/logs/laravel.log` for detailed error messages.

## Advanced Configuration

### Custom Scopes

Add additional scopes in `config/logto.php`:

```php
'scopes' => [
    'openid',
    'profile',
    'email',
    'phone',
    'offline_access',
    'roles', // For RBAC
    'urn:logto:scope:organizations', // For organizations
],
```

### API Resources

Configure API resources for access tokens:

```php
'resources' => [
    env('APP_URL') . '/api',
    'https://api.example.com',
],
```

### Custom Storage

Use custom storage instead of PHP session:

```php
'storage' => 'cache', // or 'database'
```

## Migration from Local Auth

### For Existing Users

1. Users with matching emails will be automatically linked
2. First Logto login links the account
3. Local password is preserved but not used

### For New Deployments

1. Disable local registration in frontend
2. Only show Logto sign-in button
3. Keep local auth for admin accounts (optional)

## Testing

### Manual Testing

1. Visit `/api/v1/passport/auth/logto/sign-in`
2. Copy the `sign_in_url` and open in browser
3. Complete Logto authentication
4. Verify callback creates user in database
5. Check that Sanctum token works for API calls

### API Testing with cURL

```bash
# Get sign-in URL
curl http://localhost/api/v1/passport/auth/logto/sign-in

# Check auth status
curl http://localhost/api/v1/passport/auth/logto/check
```

## Support

For issues or questions:
- Check [Logto Documentation](https://docs.logto.io)
- Review logs in `storage/logs/laravel.log`
- Open an issue on GitHub

## License

This integration follows the same MIT license as Xboard.
