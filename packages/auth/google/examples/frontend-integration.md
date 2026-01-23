# Frontend Integration Examples

This document provides examples of integrating Google OAuth 2.0 authentication in various frontend frameworks.

## Table of Contents

- [React](#react)
- [Vue.js](#vuejs)
- [Angular](#angular)
- [Vanilla JavaScript](#vanilla-javascript)

## React

### Login Component

```jsx
import React from 'react';

const GoogleLoginButton = () => {
  const handleGoogleLogin = () => {
    // Redirect to backend OAuth endpoint
    window.location.href = `${process.env.REACT_APP_API_URL}/api/auth/google/redirect`;
  };

  return (
    <button
      onClick={handleGoogleLogin}
      className="google-login-btn"
    >
      <img src="/google-icon.svg" alt="Google" />
      Sign in with Google
    </button>
  );
};

export default GoogleLoginButton;
```

### Callback Handler

```jsx
import { useEffect } from 'react';
import { useNavigate, useSearchParams } from 'react-router-dom';
import axios from 'axios';

const GoogleCallback = () => {
  const navigate = useNavigate();
  const [searchParams] = useSearchParams();

  useEffect(() => {
    const handleCallback = async () => {
      const token = searchParams.get('token');
      const error = searchParams.get('error');

      if (error) {
        console.error('Authentication error:', error);
        navigate('/login?error=' + encodeURIComponent(error));
        return;
      }

      if (token) {
        // Store token
        localStorage.setItem('auth_token', token);

        // Set default authorization header
        axios.defaults.headers.common['Authorization'] = `Bearer ${token}`;

        // Fetch user data
        try {
          const response = await axios.get(
            `${process.env.REACT_APP_API_URL}/api/user`
          );

          // Store user data in your state management (Redux, Context, etc.)
          console.log('User:', response.data);

          // Redirect to dashboard
          navigate('/dashboard');
        } catch (err) {
          console.error('Failed to fetch user:', err);
          navigate('/login');
        }
      }
    };

    handleCallback();
  }, [searchParams, navigate]);

  return (
    <div className="loading">
      <p>Authenticating with Google...</p>
    </div>
  );
};

export default GoogleCallback;
```

### Using Context API

```jsx
// AuthContext.js
import React, { createContext, useState, useContext, useEffect } from 'react';
import axios from 'axios';

const AuthContext = createContext();

export const useAuth = () => useContext(AuthContext);

export const AuthProvider = ({ children }) => {
  const [user, setUser] = useState(null);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    const token = localStorage.getItem('auth_token');
    if (token) {
      axios.defaults.headers.common['Authorization'] = `Bearer ${token}`;
      fetchUser();
    } else {
      setLoading(false);
    }
  }, []);

  const fetchUser = async () => {
    try {
      const response = await axios.get(
        `${process.env.REACT_APP_API_URL}/api/user`
      );
      setUser(response.data);
    } catch (err) {
      console.error('Failed to fetch user:', err);
      logout();
    } finally {
      setLoading(false);
    }
  };

  const logout = () => {
    localStorage.removeItem('auth_token');
    delete axios.defaults.headers.common['Authorization'];
    setUser(null);
  };

  const revokeGoogleAccess = async () => {
    try {
      await axios.delete(
        `${process.env.REACT_APP_API_URL}/api/auth/google/revoke`
      );
      logout();
    } catch (err) {
      console.error('Failed to revoke access:', err);
    }
  };

  return (
    <AuthContext.Provider
      value={{
        user,
        loading,
        logout,
        revokeGoogleAccess,
      }}
    >
      {children}
    </AuthContext.Provider>
  );
};
```

## Vue.js

### Login Component

```vue
<!-- components/GoogleLogin.vue -->
<template>
  <button @click="loginWithGoogle" class="google-login-btn">
    <img src="/google-icon.svg" alt="Google" />
    Sign in with Google
  </button>
</template>

<script setup>
const loginWithGoogle = () => {
  const apiUrl = import.meta.env.VITE_API_URL;
  window.location.href = `${apiUrl}/api/auth/google/redirect`;
};
</script>

<style scoped>
.google-login-btn {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.5rem 1rem;
  border: 1px solid #ddd;
  border-radius: 4px;
  background: white;
  cursor: pointer;
}
</style>
```

### Callback Handler

```vue
<!-- pages/auth/Callback.vue -->
<template>
  <div class="callback-container">
    <p>Authenticating with Google...</p>
  </div>
</template>

<script setup>
import { onMounted } from 'vue';
import { useRouter } from 'vue-router';
import { useAuthStore } from '@/stores/auth';

const router = useRouter();
const authStore = useAuthStore();

onMounted(async () => {
  const params = new URLSearchParams(window.location.search);
  const token = params.get('token');
  const error = params.get('error');

  if (error) {
    console.error('Authentication error:', error);
    router.push({ name: 'login', query: { error } });
    return;
  }

  if (token) {
    try {
      await authStore.setToken(token);
      await authStore.fetchUser();
      router.push({ name: 'dashboard' });
    } catch (err) {
      console.error('Failed to authenticate:', err);
      router.push({ name: 'login' });
    }
  }
});
</script>
```

### Pinia Store

```javascript
// stores/auth.js
import { defineStore } from 'pinia';
import axios from 'axios';

export const useAuthStore = defineStore('auth', {
  state: () => ({
    user: null,
    token: localStorage.getItem('auth_token'),
  }),

  getters: {
    isAuthenticated: (state) => !!state.token,
    hasGoogleAccount: (state) => state.user?.google_user != null,
  },

  actions: {
    async setToken(token) {
      this.token = token;
      localStorage.setItem('auth_token', token);
      axios.defaults.headers.common['Authorization'] = `Bearer ${token}`;
    },

    async fetchUser() {
      const apiUrl = import.meta.env.VITE_API_URL;
      const response = await axios.get(`${apiUrl}/api/user`);
      this.user = response.data;
    },

    async logout() {
      this.token = null;
      this.user = null;
      localStorage.removeItem('auth_token');
      delete axios.defaults.headers.common['Authorization'];
    },

    async revokeGoogleAccess() {
      const apiUrl = import.meta.env.VITE_API_URL;
      await axios.delete(`${apiUrl}/api/auth/google/revoke`);
      await this.logout();
    },
  },
});
```

## Angular

### Service

```typescript
// services/auth.service.ts
import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Router } from '@angular/router';
import { BehaviorSubject, Observable } from 'rxjs';
import { tap } from 'rxjs/operators';
import { environment } from '../environments/environment';

interface User {
  id: number;
  name: string;
  email: string;
  google_user?: any;
}

@Injectable({
  providedIn: 'root'
})
export class AuthService {
  private userSubject = new BehaviorSubject<User | null>(null);
  public user$ = this.userSubject.asObservable();

  constructor(
    private http: HttpClient,
    private router: Router
  ) {
    const token = localStorage.getItem('auth_token');
    if (token) {
      this.fetchUser().subscribe();
    }
  }

  loginWithGoogle(): void {
    window.location.href = `${environment.apiUrl}/api/auth/google/redirect`;
  }

  handleCallback(token: string): Observable<User> {
    localStorage.setItem('auth_token', token);
    return this.fetchUser();
  }

  fetchUser(): Observable<User> {
    return this.http.get<User>(`${environment.apiUrl}/api/user`).pipe(
      tap(user => this.userSubject.next(user))
    );
  }

  logout(): void {
    localStorage.removeItem('auth_token');
    this.userSubject.next(null);
    this.router.navigate(['/login']);
  }

  revokeGoogleAccess(): Observable<any> {
    return this.http.delete(`${environment.apiUrl}/api/auth/google/revoke`).pipe(
      tap(() => this.logout())
    );
  }

  getToken(): string | null {
    return localStorage.getItem('auth_token');
  }
}
```

### Component

```typescript
// components/google-login/google-login.component.ts
import { Component } from '@angular/core';
import { AuthService } from '../../services/auth.service';

@Component({
  selector: 'app-google-login',
  template: `
    <button (click)="loginWithGoogle()" class="google-login-btn">
      <img src="/assets/google-icon.svg" alt="Google" />
      Sign in with Google
    </button>
  `,
  styles: [`
    .google-login-btn {
      display: flex;
      align-items: center;
      gap: 0.5rem;
      padding: 0.5rem 1rem;
      border: 1px solid #ddd;
      border-radius: 4px;
      background: white;
      cursor: pointer;
    }
  `]
})
export class GoogleLoginComponent {
  constructor(private authService: AuthService) {}

  loginWithGoogle(): void {
    this.authService.loginWithGoogle();
  }
}
```

### Callback Component

```typescript
// components/auth-callback/auth-callback.component.ts
import { Component, OnInit } from '@angular/core';
import { ActivatedRoute, Router } from '@angular/router';
import { AuthService } from '../../services/auth.service';

@Component({
  selector: 'app-auth-callback',
  template: `
    <div class="callback-container">
      <p>Authenticating with Google...</p>
    </div>
  `
})
export class AuthCallbackComponent implements OnInit {
  constructor(
    private route: ActivatedRoute,
    private router: Router,
    private authService: AuthService
  ) {}

  ngOnInit(): void {
    this.route.queryParams.subscribe(params => {
      const token = params['token'];
      const error = params['error'];

      if (error) {
        console.error('Authentication error:', error);
        this.router.navigate(['/login'], { queryParams: { error } });
        return;
      }

      if (token) {
        this.authService.handleCallback(token).subscribe({
          next: () => {
            this.router.navigate(['/dashboard']);
          },
          error: (err) => {
            console.error('Failed to authenticate:', err);
            this.router.navigate(['/login']);
          }
        });
      }
    });
  }
}
```

### HTTP Interceptor

```typescript
// interceptors/auth.interceptor.ts
import { Injectable } from '@angular/core';
import {
  HttpRequest,
  HttpHandler,
  HttpEvent,
  HttpInterceptor
} from '@angular/common/http';
import { Observable } from 'rxjs';
import { AuthService } from '../services/auth.service';

@Injectable()
export class AuthInterceptor implements HttpInterceptor {
  constructor(private authService: AuthService) {}

  intercept(
    request: HttpRequest<unknown>,
    next: HttpHandler
  ): Observable<HttpEvent<unknown>> {
    const token = this.authService.getToken();

    if (token) {
      request = request.clone({
        setHeaders: {
          Authorization: `Bearer ${token}`
        }
      });
    }

    return next.handle(request);
  }
}
```

## Vanilla JavaScript

```html
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Google Login</title>
</head>
<body>
  <div id="app">
    <div id="login-container">
      <button id="google-login-btn">
        <img src="/google-icon.svg" alt="Google" />
        Sign in with Google
      </button>
    </div>

    <div id="user-container" style="display: none;">
      <h2>Welcome, <span id="user-name"></span>!</h2>
      <button id="logout-btn">Logout</button>
      <button id="revoke-btn">Revoke Google Access</button>
    </div>
  </div>

  <script>
    const API_URL = 'http://localhost:8000';

    // Check for callback parameters
    const urlParams = new URLSearchParams(window.location.search);
    const token = urlParams.get('token');
    const error = urlParams.get('error');

    if (error) {
      alert('Authentication error: ' + error);
      window.location.href = '/login';
    } else if (token) {
      handleAuthSuccess(token);
    }

    // Initialize page
    document.addEventListener('DOMContentLoaded', () => {
      const storedToken = localStorage.getItem('auth_token');
      if (storedToken) {
        fetchUser(storedToken);
      }

      // Google login button
      document.getElementById('google-login-btn').addEventListener('click', () => {
        window.location.href = `${API_URL}/api/auth/google/redirect`;
      });

      // Logout button
      document.getElementById('logout-btn').addEventListener('click', logout);

      // Revoke button
      document.getElementById('revoke-btn').addEventListener('click', revokeGoogleAccess);
    });

    async function handleAuthSuccess(token) {
      localStorage.setItem('auth_token', token);
      await fetchUser(token);
      // Clean URL
      window.history.replaceState({}, document.title, '/');
    }

    async function fetchUser(token) {
      try {
        const response = await fetch(`${API_URL}/api/user`, {
          headers: {
            'Authorization': `Bearer ${token}`,
            'Accept': 'application/json'
          }
        });

        if (!response.ok) {
          throw new Error('Failed to fetch user');
        }

        const user = await response.json();
        displayUser(user);
      } catch (err) {
        console.error('Error fetching user:', err);
        logout();
      }
    }

    function displayUser(user) {
      document.getElementById('login-container').style.display = 'none';
      document.getElementById('user-container').style.display = 'block';
      document.getElementById('user-name').textContent = user.name;
    }

    function logout() {
      localStorage.removeItem('auth_token');
      document.getElementById('login-container').style.display = 'block';
      document.getElementById('user-container').style.display = 'none';
    }

    async function revokeGoogleAccess() {
      const token = localStorage.getItem('auth_token');
      if (!token) return;

      try {
        const response = await fetch(`${API_URL}/api/auth/google/revoke`, {
          method: 'DELETE',
          headers: {
            'Authorization': `Bearer ${token}`,
            'Accept': 'application/json'
          }
        });

        if (response.ok) {
          alert('Google access revoked successfully');
          logout();
        }
      } catch (err) {
        console.error('Error revoking access:', err);
        alert('Failed to revoke Google access');
      }
    }
  </script>
</body>
</html>
```

## Mobile Apps

### React Native

```javascript
import { useEffect } from 'react';
import { Linking } from 'react-native';
import * as WebBrowser from 'expo-web-browser';

const GoogleLogin = () => {
  useEffect(() => {
    // Handle deep link callback
    const handleDeepLink = ({ url }) => {
      const params = new URL(url).searchParams;
      const token = params.get('token');
      if (token) {
        // Store token and navigate
        AsyncStorage.setItem('auth_token', token);
        // Navigate to dashboard
      }
    };

    Linking.addEventListener('url', handleDeepLink);
    return () => Linking.removeEventListener('url', handleDeepLink);
  }, []);

  const loginWithGoogle = async () => {
    const result = await WebBrowser.openAuthSessionAsync(
      `${API_URL}/api/auth/google/redirect`,
      'yourapp://auth/callback'
    );

    if (result.type === 'success') {
      // Handle success
    }
  };

  return (
    <Button title="Sign in with Google" onPress={loginWithGoogle} />
  );
};
```

## Environment Variables

Create a `.env` file in your frontend project:

```env
# React
REACT_APP_API_URL=http://localhost:8000

# Vue/Vite
VITE_API_URL=http://localhost:8000

# Angular
# environment.ts
export const environment = {
  production: false,
  apiUrl: 'http://localhost:8000'
};
```

## CORS Configuration

Make sure your Laravel backend allows requests from your frontend:

```php
// config/cors.php
return [
    'paths' => ['api/*', 'sanctum/csrf-cookie'],
    'allowed_methods' => ['*'],
    'allowed_origins' => [
        'http://localhost:3000',
        'http://localhost:4200',
        // Add your production URLs
    ],
    'allowed_headers' => ['*'],
    'exposed_headers' => [],
    'max_age' => 0,
    'supports_credentials' => true,
];
```
