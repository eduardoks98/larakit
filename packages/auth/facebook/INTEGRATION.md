# Facebook Auth Integration Guide

This guide shows how to integrate Facebook authentication in your frontend application.

## Flow Overview

1. User clicks "Login with Facebook" button
2. Frontend requests authorization URL from backend
3. User is redirected to Facebook for authentication
4. Facebook redirects back to your callback endpoint
5. Backend exchanges code for access token
6. Backend creates/finds user and generates Sanctum token
7. User is redirected to frontend with token
8. Frontend stores token and makes authenticated requests

## Frontend Integration

### React/Next.js Example

```javascript
import { useState } from 'react';

export default function FacebookLogin() {
  const [loading, setLoading] = useState(false);

  const handleFacebookLogin = async () => {
    try {
      setLoading(true);

      // Get Facebook authorization URL
      const response = await fetch('http://localhost:8000/api/facebook-auth/redirect');
      const { data } = await response.json();

      // Redirect to Facebook
      window.location.href = data.authorization_url;
    } catch (error) {
      console.error('Facebook login failed:', error);
      setLoading(false);
    }
  };

  return (
    <button
      onClick={handleFacebookLogin}
      disabled={loading}
      className="facebook-login-button"
    >
      {loading ? 'Loading...' : 'Login with Facebook'}
    </button>
  );
}
```

### Callback Handler (React/Next.js)

```javascript
// pages/auth/callback.js or app/auth/callback/page.js
import { useEffect } from 'react';
import { useRouter, useSearchParams } from 'next/navigation';

export default function AuthCallback() {
  const router = useRouter();
  const searchParams = useSearchParams();

  useEffect(() => {
    const token = searchParams.get('token');

    if (token) {
      // Store token in localStorage or cookie
      localStorage.setItem('auth_token', token);

      // Redirect to dashboard or home
      router.push('/dashboard');
    } else {
      // Handle error
      router.push('/login?error=auth_failed');
    }
  }, [searchParams, router]);

  return (
    <div className="flex items-center justify-center min-h-screen">
      <div className="text-center">
        <h1 className="text-2xl font-bold mb-4">Authenticating...</h1>
        <p>Please wait while we complete your login.</p>
      </div>
    </div>
  );
}
```

### Vue.js Example

```vue
<template>
  <div>
    <button
      @click="handleFacebookLogin"
      :disabled="loading"
      class="facebook-login-button"
    >
      {{ loading ? 'Loading...' : 'Login with Facebook' }}
    </button>
  </div>
</template>

<script setup>
import { ref } from 'vue';

const loading = ref(false);

const handleFacebookLogin = async () => {
  try {
    loading.value = true;

    // Get Facebook authorization URL
    const response = await fetch('http://localhost:8000/api/facebook-auth/redirect');
    const { data } = await response.json();

    // Redirect to Facebook
    window.location.href = data.authorization_url;
  } catch (error) {
    console.error('Facebook login failed:', error);
    loading.value = false;
  }
};
</script>
```

### Angular Example

```typescript
import { Component } from '@angular/core';
import { HttpClient } from '@angular/common/http';

@Component({
  selector: 'app-facebook-login',
  template: `
    <button
      (click)="handleFacebookLogin()"
      [disabled]="loading"
      class="facebook-login-button"
    >
      {{ loading ? 'Loading...' : 'Login with Facebook' }}
    </button>
  `
})
export class FacebookLoginComponent {
  loading = false;

  constructor(private http: HttpClient) {}

  async handleFacebookLogin() {
    try {
      this.loading = true;

      // Get Facebook authorization URL
      const response = await this.http
        .get<any>('http://localhost:8000/api/facebook-auth/redirect')
        .toPromise();

      // Redirect to Facebook
      window.location.href = response.data.authorization_url;
    } catch (error) {
      console.error('Facebook login failed:', error);
      this.loading = false;
    }
  }
}
```

## Making Authenticated Requests

### Fetch API

```javascript
const token = localStorage.getItem('auth_token');

const response = await fetch('http://localhost:8000/api/user', {
  headers: {
    'Authorization': `Bearer ${token}`,
    'Accept': 'application/json',
  }
});

const user = await response.json();
```

### Axios

```javascript
import axios from 'axios';

const api = axios.create({
  baseURL: 'http://localhost:8000/api',
  headers: {
    'Accept': 'application/json',
  }
});

// Add token to all requests
api.interceptors.request.use((config) => {
  const token = localStorage.getItem('auth_token');
  if (token) {
    config.headers.Authorization = `Bearer ${token}`;
  }
  return config;
});

// Get user profile
const { data } = await api.get('/user');

// Get Facebook profile
const { data: facebookProfile } = await api.get('/facebook-auth/profile');
```

## Getting User Profile

```javascript
// Get authenticated user
const getUserProfile = async () => {
  const token = localStorage.getItem('auth_token');

  const response = await fetch('http://localhost:8000/api/user', {
    headers: {
      'Authorization': `Bearer ${token}`,
      'Accept': 'application/json',
    }
  });

  return await response.json();
};

// Get Facebook profile
const getFacebookProfile = async () => {
  const token = localStorage.getItem('auth_token');

  const response = await fetch('http://localhost:8000/api/facebook-auth/profile', {
    headers: {
      'Authorization': `Bearer ${token}`,
      'Accept': 'application/json',
    }
  });

  return await response.json();
};

// Usage
const user = await getUserProfile();
const facebookProfile = await getFacebookProfile();

console.log('User:', user);
console.log('Facebook Profile:', facebookProfile);
```

## Disconnecting Facebook Account

```javascript
const disconnectFacebook = async () => {
  const token = localStorage.getItem('auth_token');

  const response = await fetch('http://localhost:8000/api/facebook-auth/disconnect', {
    method: 'DELETE',
    headers: {
      'Authorization': `Bearer ${token}`,
      'Accept': 'application/json',
    }
  });

  const result = await response.json();
  console.log(result.message); // "Facebook account disconnected successfully"
};
```

## Error Handling

```javascript
const handleFacebookLogin = async () => {
  try {
    setLoading(true);

    const response = await fetch('http://localhost:8000/api/facebook-auth/redirect');

    if (!response.ok) {
      throw new Error('Failed to get authorization URL');
    }

    const { data } = await response.json();
    window.location.href = data.authorization_url;
  } catch (error) {
    console.error('Facebook login failed:', error);

    // Show error to user
    alert('Failed to initiate Facebook login. Please try again.');

    setLoading(false);
  }
};
```

## Complete React Context Example

```javascript
// contexts/AuthContext.js
import { createContext, useContext, useState, useEffect } from 'react';

const AuthContext = createContext({});

export function AuthProvider({ children }) {
  const [user, setUser] = useState(null);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    // Check for existing token on mount
    const token = localStorage.getItem('auth_token');
    if (token) {
      fetchUser(token);
    } else {
      setLoading(false);
    }
  }, []);

  const fetchUser = async (token) => {
    try {
      const response = await fetch('http://localhost:8000/api/user', {
        headers: {
          'Authorization': `Bearer ${token}`,
          'Accept': 'application/json',
        }
      });

      if (response.ok) {
        const userData = await response.json();
        setUser(userData);
      } else {
        // Token invalid, clear it
        localStorage.removeItem('auth_token');
      }
    } catch (error) {
      console.error('Failed to fetch user:', error);
      localStorage.removeItem('auth_token');
    } finally {
      setLoading(false);
    }
  };

  const loginWithFacebook = async () => {
    try {
      const response = await fetch('http://localhost:8000/api/facebook-auth/redirect');
      const { data } = await response.json();
      window.location.href = data.authorization_url;
    } catch (error) {
      console.error('Facebook login failed:', error);
      throw error;
    }
  };

  const logout = () => {
    localStorage.removeItem('auth_token');
    setUser(null);
  };

  return (
    <AuthContext.Provider value={{ user, loading, loginWithFacebook, logout }}>
      {children}
    </AuthContext.Provider>
  );
}

export const useAuth = () => useContext(AuthContext);
```

```javascript
// Usage in components
import { useAuth } from '@/contexts/AuthContext';

export default function LoginPage() {
  const { loginWithFacebook, loading } = useAuth();

  return (
    <div>
      <h1>Login</h1>
      <button onClick={loginWithFacebook} disabled={loading}>
        Login with Facebook
      </button>
    </div>
  );
}
```

## Environment Variables (Frontend)

```env
# .env.local (Next.js) or .env (React/Vue)
NEXT_PUBLIC_API_URL=http://localhost:8000
NEXT_PUBLIC_FRONTEND_URL=http://localhost:3000
```

## Security Best Practices

1. Always use HTTPS in production
2. Store tokens securely (httpOnly cookies preferred over localStorage)
3. Implement token refresh mechanism
4. Validate state parameter for CSRF protection
5. Handle token expiration gracefully
6. Clear tokens on logout
7. Don't expose tokens in URLs or logs

## Testing

```javascript
// Mock Facebook login in tests
jest.mock('next/navigation', () => ({
  useRouter: () => ({
    push: jest.fn(),
  }),
  useSearchParams: () => ({
    get: (key) => (key === 'token' ? 'mock-token' : null),
  }),
}));

test('stores token and redirects on successful callback', async () => {
  render(<AuthCallback />);

  await waitFor(() => {
    expect(localStorage.getItem('auth_token')).toBe('mock-token');
  });
});
```
