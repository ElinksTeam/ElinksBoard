# Frontend Logto Integration Guide

## Overview

This guide explains how to integrate Logto authentication into the Xboard frontend (Vue3).

## Changes Required

### 1. Remove Traditional Login Form

**Before:**
```vue
<template>
  <form @submit.prevent="handleLogin">
    <input v-model="email" type="email" placeholder="Email" />
    <input v-model="password" type="password" placeholder="Password" />
    <button type="submit">Login</button>
  </form>
</template>

<script setup>
import { ref } from 'vue'
import axios from 'axios'

const email = ref('')
const password = ref('')

async function handleLogin() {
  const { data } = await axios.post('/api/v1/passport/auth/login', {
    email: email.value,
    password: password.value
  })
  // Handle response...
}
</script>
```

**After:**
```vue
<template>
  <div class="login-container">
    <h1>Welcome to Xboard</h1>
    <button @click="handleLogtoLogin" class="logto-login-btn">
      Sign in with Logto
    </button>
  </div>
</template>

<script setup>
import { useLogtoAuth } from '@/composables/useLogtoAuth'

const { signIn } = useLogtoAuth()

function handleLogtoLogin() {
  signIn()
}
</script>
```

### 2. Create Logto Composable

Create `composables/useLogtoAuth.ts`:

```typescript
import { ref } from 'vue'
import axios from 'axios'
import { useRouter } from 'vue-router'

export function useLogtoAuth() {
  const router = useRouter()
  const isAuthenticated = ref(false)
  const user = ref(null)
  const loading = ref(false)
  const error = ref(null)

  /**
   * Initiate Logto sign-in
   */
  async function signIn() {
    try {
      loading.value = true
      error.value = null
      
      const { data } = await axios.get('/api/v1/passport/auth/logto/sign-in')
      
      if (data.code === 0) {
        // Redirect to Logto sign-in page
        window.location.href = data.data.sign_in_url
      } else {
        throw new Error(data.message || 'Failed to initiate sign-in')
      }
    } catch (err) {
      error.value = err.message
      console.error('Sign-in failed:', err)
      
      // Show error notification
      // notification.error({
      //   title: 'Sign-in Failed',
      //   content: err.message
      // })
    } finally {
      loading.value = false
    }
  }

  /**
   * Handle Logto callback
   * Call this in the callback route component
   */
  async function handleCallback() {
    try {
      loading.value = true
      error.value = null
      
      // Get callback parameters from URL
      const queryString = window.location.search
      
      const { data } = await axios.get(
        '/api/v1/passport/auth/logto/callback' + queryString
      )
      
      if (data.code === 0) {
        // Save authentication data
        localStorage.setItem('auth_token', data.data.auth_data)
        localStorage.setItem('user', JSON.stringify(data.data.user))
        
        // Update state
        isAuthenticated.value = true
        user.value = data.data.user
        
        // Redirect to dashboard
        router.push('/')
        
        return data.data.user
      } else {
        throw new Error(data.message || 'Authentication failed')
      }
    } catch (err) {
      error.value = err.message
      console.error('Callback failed:', err)
      
      // Redirect to login page on error
      router.push('/login')
      
      throw err
    } finally {
      loading.value = false
    }
  }

  /**
   * Sign out
   */
  async function signOut() {
    try {
      loading.value = true
      error.value = null
      
      const { data } = await axios.post('/api/v1/passport/auth/logto/sign-out', {}, {
        headers: {
          'Authorization': localStorage.getItem('auth_token')
        }
      })
      
      // Clear local storage
      localStorage.removeItem('auth_token')
      localStorage.removeItem('user')
      
      // Update state
      isAuthenticated.value = false
      user.value = null
      
      if (data.code === 0) {
        // Redirect to Logto sign-out page
        window.location.href = data.data.sign_out_url
      } else {
        // Just redirect to login page
        router.push('/login')
      }
    } catch (err) {
      error.value = err.message
      console.error('Sign-out failed:', err)
      
      // Clear local data anyway
      localStorage.removeItem('auth_token')
      localStorage.removeItem('user')
      router.push('/login')
    } finally {
      loading.value = false
    }
  }

  /**
   * Check authentication status
   */
  async function checkAuth() {
    try {
      const { data } = await axios.get('/api/v1/passport/auth/logto/check')
      
      if (data.code === 0) {
        isAuthenticated.value = data.data.is_authenticated
        if (data.data.user_id) {
          user.value = {
            id: data.data.user_id,
            email: data.data.email,
            is_admin: data.data.is_admin
          }
        }
      }
      
      return data.data.is_authenticated
    } catch (err) {
      console.error('Check auth failed:', err)
      return false
    }
  }

  /**
   * Get current user info
   */
  async function getUserInfo() {
    try {
      const { data } = await axios.get('/api/v1/passport/auth/logto/userinfo', {
        headers: {
          'Authorization': localStorage.getItem('auth_token')
        }
      })
      
      if (data.code === 0) {
        user.value = data.data.user
        return data.data.user
      }
      
      return null
    } catch (err) {
      console.error('Get user info failed:', err)
      return null
    }
  }

  return {
    isAuthenticated,
    user,
    loading,
    error,
    signIn,
    signOut,
    handleCallback,
    checkAuth,
    getUserInfo
  }
}
```

### 3. Create Callback Route Component

Create `views/auth/Callback.vue`:

```vue
<template>
  <div class="callback-container">
    <div v-if="loading" class="loading">
      <div class="spinner"></div>
      <p>Signing in...</p>
    </div>
    
    <div v-if="error" class="error">
      <p>Authentication failed: {{ error }}</p>
      <button @click="router.push('/login')">Back to Login</button>
    </div>
  </div>
</template>

<script setup lang="ts">
import { onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useLogtoAuth } from '@/composables/useLogtoAuth'

const router = useRouter()
const { handleCallback, loading, error } = useLogtoAuth()

onMounted(async () => {
  try {
    await handleCallback()
  } catch (err) {
    console.error('Callback error:', err)
  }
})
</script>

<style scoped>
.callback-container {
  display: flex;
  justify-content: center;
  align-items: center;
  min-height: 100vh;
}

.loading {
  text-align: center;
}

.spinner {
  width: 40px;
  height: 40px;
  border: 4px solid #f3f3f3;
  border-top: 4px solid #3498db;
  border-radius: 50%;
  animation: spin 1s linear infinite;
  margin: 0 auto 20px;
}

@keyframes spin {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}

.error {
  text-align: center;
  color: #e74c3c;
}
</style>
```

### 4. Update Router Configuration

Add callback route in `router/index.ts`:

```typescript
import { createRouter, createWebHistory } from 'vue-router'
import Login from '@/views/auth/Login.vue'
import Callback from '@/views/auth/Callback.vue'

const routes = [
  {
    path: '/login',
    name: 'Login',
    component: Login,
    meta: { requiresAuth: false }
  },
  {
    path: '/callback',
    name: 'Callback',
    component: Callback,
    meta: { requiresAuth: false }
  },
  {
    path: '/',
    name: 'Dashboard',
    component: () => import('@/views/Dashboard.vue'),
    meta: { requiresAuth: true }
  },
  // ... other routes
]

const router = createRouter({
  history: createWebHistory(),
  routes
})

// Navigation guard
router.beforeEach((to, from, next) => {
  const token = localStorage.getItem('auth_token')
  
  if (to.meta.requiresAuth && !token) {
    next('/login')
  } else if (to.path === '/login' && token) {
    next('/')
  } else {
    next()
  }
})

export default router
```

### 5. Update Axios Interceptor

Add authentication header in `plugins/axios.ts`:

```typescript
import axios from 'axios'
import { useRouter } from 'vue-router'

// Request interceptor
axios.interceptors.request.use(
  (config) => {
    const token = localStorage.getItem('auth_token')
    if (token) {
      config.headers.Authorization = token
    }
    return config
  },
  (error) => {
    return Promise.reject(error)
  }
)

// Response interceptor
axios.interceptors.response.use(
  (response) => {
    return response
  },
  (error) => {
    if (error.response?.status === 401 || error.response?.status === 403) {
      // Clear auth data
      localStorage.removeItem('auth_token')
      localStorage.removeItem('user')
      
      // Redirect to login
      const router = useRouter()
      router.push('/login')
    }
    return Promise.reject(error)
  }
)

export default axios
```

### 6. Update Login Page

Replace traditional login form with Logto button:

```vue
<template>
  <div class="login-page">
    <div class="login-card">
      <div class="logo">
        <img src="/logo.svg" alt="Xboard" />
      </div>
      
      <h1>Welcome to Xboard</h1>
      <p class="subtitle">Sign in to continue</p>
      
      <button 
        @click="handleLogin" 
        :disabled="loading"
        class="logto-btn"
      >
        <span v-if="!loading">Sign in with Logto</span>
        <span v-else>Redirecting...</span>
      </button>
      
      <div v-if="error" class="error-message">
        {{ error }}
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { useLogtoAuth } from '@/composables/useLogtoAuth'

const { signIn, loading, error } = useLogtoAuth()

function handleLogin() {
  signIn()
}
</script>

<style scoped>
.login-page {
  display: flex;
  justify-content: center;
  align-items: center;
  min-height: 100vh;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

.login-card {
  background: white;
  padding: 40px;
  border-radius: 12px;
  box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
  max-width: 400px;
  width: 100%;
  text-align: center;
}

.logo img {
  width: 80px;
  height: 80px;
  margin-bottom: 20px;
}

h1 {
  font-size: 24px;
  margin-bottom: 8px;
  color: #333;
}

.subtitle {
  color: #666;
  margin-bottom: 32px;
}

.logto-btn {
  width: 100%;
  padding: 14px 24px;
  font-size: 16px;
  font-weight: 600;
  color: white;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  border: none;
  border-radius: 8px;
  cursor: pointer;
  transition: transform 0.2s, box-shadow 0.2s;
}

.logto-btn:hover:not(:disabled) {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
}

.logto-btn:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.error-message {
  margin-top: 16px;
  padding: 12px;
  background: #fee;
  color: #c33;
  border-radius: 6px;
  font-size: 14px;
}
</style>
```

## Testing

### 1. Test Sign-in Flow

1. Navigate to `/login`
2. Click "Sign in with Logto"
3. Should redirect to Logto sign-in page
4. Complete authentication
5. Should redirect back to `/callback`
6. Should automatically redirect to dashboard

### 2. Test Sign-out Flow

1. Click sign-out button
2. Should clear local storage
3. Should redirect to Logto sign-out page
4. Should redirect back to login page

### 3. Test Protected Routes

1. Try accessing dashboard without authentication
2. Should redirect to login page
3. After login, should be able to access dashboard

## Error Handling

### Common Errors

1. **"Logto 认证系统未配置"**
   - Logto is not configured in admin panel
   - Admin needs to configure Logto settings

2. **"Invalid redirect URI"**
   - Redirect URI mismatch between frontend and Logto Console
   - Check Logto Console settings

3. **"Authentication failed"**
   - Network error or invalid credentials
   - Check browser console for details

## Migration Checklist

- [ ] Remove traditional login form components
- [ ] Create `useLogtoAuth` composable
- [ ] Create callback route component
- [ ] Update router configuration
- [ ] Update axios interceptors
- [ ] Update login page
- [ ] Test sign-in flow
- [ ] Test sign-out flow
- [ ] Test protected routes
- [ ] Update user profile components
- [ ] Remove password change functionality (handled by Logto)

## Notes

1. **No Password Management**: Users manage passwords through Logto
2. **No Registration Form**: Users register through Logto
3. **No Password Reset**: Handled by Logto
4. **Session Management**: Handled by Logto + Sanctum tokens
5. **User Profile**: Basic info from Logto, business data from Xboard

## Support

For issues or questions:
- Check browser console for errors
- Review network requests in DevTools
- Check backend logs
- Verify Logto Console configuration
