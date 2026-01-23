# Quick Start Guide

Get up and running with Facebook authentication in 5 minutes.

## 1. Installation

```bash
# Add the package
composer require eduardoks98/facebook-auth

# Publish config and migrations
php artisan vendor:publish --tag=facebook-auth-config
php artisan vendor:publish --tag=facebook-auth-migrations

# Run migrations
php artisan migrate
```

## 2. Facebook App Setup

1. Go to [Facebook Developers](https://developers.facebook.com/apps)
2. Click "Create App" → Select "Consumer" type
3. Fill in app details and create
4. Add "Facebook Login" product
5. Configure Settings:
   - **Valid OAuth Redirect URIs**: `https://yourdomain.com/api/facebook-auth/callback`
   - **App Domains**: `yourdomain.com`
6. Get credentials from Settings → Basic:
   - App ID
   - App Secret

## 3. Environment Configuration

Add to your `.env` file:

```env
FACEBOOK_APP_ID=your-facebook-app-id
FACEBOOK_APP_SECRET=your-facebook-app-secret
FACEBOOK_REDIRECT_URI="${APP_URL}/api/facebook-auth/callback"
FACEBOOK_FRONTEND_REDIRECT_URL="${FRONTEND_URL}/auth/callback"
```

## 4. Update User Model

Add the relationship to `app/Models/User.php`:

```php
use Eduardoks98\FacebookAuth\Models\FacebookUser;

class User extends Authenticatable
{
    use HasApiTokens; // Make sure this is imported from Laravel\Sanctum

    // Add this relationship
    public function facebookUser()
    {
        return $this->hasOne(FacebookUser::class);
    }
}
```

## 5. Frontend Integration

### React Example

```jsx
// Login button
const handleLogin = async () => {
  const response = await fetch('http://localhost:8000/api/facebook-auth/redirect');
  const { data } = await response.json();
  window.location.href = data.authorization_url;
};

<button onClick={handleLogin}>Login with Facebook</button>

// Callback page (e.g., /auth/callback)
useEffect(() => {
  const token = new URLSearchParams(window.location.search).get('token');
  if (token) {
    localStorage.setItem('token', token);
    // Redirect to dashboard
    window.location.href = '/dashboard';
  }
}, []);
```

### Vue Example

```vue
<template>
  <button @click="loginWithFacebook">Login with Facebook</button>
</template>

<script setup>
const loginWithFacebook = async () => {
  const response = await fetch('http://localhost:8000/api/facebook-auth/redirect');
  const { data } = await response.json();
  window.location.href = data.authorization_url;
};
</script>
```

## 6. Test the Integration

1. Start your Laravel backend:
```bash
php artisan serve
```

2. Start your frontend app:
```bash
npm run dev
```

3. Click "Login with Facebook" button
4. Authorize the app on Facebook
5. You'll be redirected back with a token
6. Use the token to make authenticated requests

## 7. Make Authenticated Requests

```javascript
const token = localStorage.getItem('token');

// Get user profile
const response = await fetch('http://localhost:8000/api/user', {
  headers: {
    'Authorization': `Bearer ${token}`,
    'Accept': 'application/json',
  }
});

const user = await response.json();
console.log(user);
```

## Available Endpoints

| Method | Endpoint | Description | Auth Required |
|--------|----------|-------------|---------------|
| GET | `/api/facebook-auth/redirect` | Get Facebook authorization URL | No |
| GET | `/api/facebook-auth/callback` | Handle Facebook callback | No |
| GET | `/api/facebook-auth/profile` | Get Facebook profile | Yes |
| DELETE | `/api/facebook-auth/disconnect` | Disconnect Facebook account | Yes |

## Common Issues

### Issue: Invalid Redirect URI

**Solution:** Make sure the redirect URI in your `.env` matches exactly what's configured in Facebook App settings.

```env
FACEBOOK_REDIRECT_URI=https://yourdomain.com/api/facebook-auth/callback
```

### Issue: 401 Unauthorized

**Solution:** Make sure you're sending the Bearer token:

```javascript
headers: {
  'Authorization': `Bearer ${token}`,
}
```

### Issue: CORS Error

**Solution:** Configure CORS in `config/cors.php`:

```php
'paths' => ['api/*'],
'allowed_origins' => [env('FRONTEND_URL', 'http://localhost:3000')],
'supports_credentials' => true,
```

## Next Steps

- [Read full documentation](README.md)
- [View integration examples](INTEGRATION.md)
- [Explore advanced usage](EXAMPLES.md)
- [Check security best practices](SECURITY.md)
- [Troubleshooting guide](TROUBLESHOOTING.md)

## Complete Flow Example

### Backend (Laravel)

```php
// Already configured! Just use the package routes
```

### Frontend (React with Context)

```jsx
// contexts/AuthContext.jsx
import { createContext, useContext, useState, useEffect } from 'react';

const AuthContext = createContext();

export function AuthProvider({ children }) {
  const [user, setUser] = useState(null);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    const token = localStorage.getItem('token');
    if (token) {
      fetchUser(token);
    } else {
      setLoading(false);
    }
  }, []);

  const fetchUser = async (token) => {
    const response = await fetch('http://localhost:8000/api/user', {
      headers: { 'Authorization': `Bearer ${token}` }
    });
    if (response.ok) {
      setUser(await response.json());
    }
    setLoading(false);
  };

  const loginWithFacebook = async () => {
    const response = await fetch('http://localhost:8000/api/facebook-auth/redirect');
    const { data } = await response.json();
    window.location.href = data.authorization_url;
  };

  const logout = () => {
    localStorage.removeItem('token');
    setUser(null);
  };

  return (
    <AuthContext.Provider value={{ user, loading, loginWithFacebook, logout }}>
      {children}
    </AuthContext.Provider>
  );
}

export const useAuth = () => useContext(AuthContext);

// pages/Login.jsx
import { useAuth } from '../contexts/AuthContext';

export default function Login() {
  const { loginWithFacebook } = useAuth();

  return (
    <div>
      <h1>Login</h1>
      <button onClick={loginWithFacebook}>Login with Facebook</button>
    </div>
  );
}

// pages/Callback.jsx
import { useEffect } from 'react';
import { useNavigate } from 'react-router-dom';

export default function Callback() {
  const navigate = useNavigate();

  useEffect(() => {
    const params = new URLSearchParams(window.location.search);
    const token = params.get('token');
    if (token) {
      localStorage.setItem('token', token);
      navigate('/dashboard');
    }
  }, []);

  return <div>Loading...</div>;
}

// pages/Dashboard.jsx
import { useAuth } from '../contexts/AuthContext';

export default function Dashboard() {
  const { user, logout } = useAuth();

  if (!user) return <div>Loading...</div>;

  return (
    <div>
      <h1>Welcome, {user.name}!</h1>
      <button onClick={logout}>Logout</button>
    </div>
  );
}
```

## Production Checklist

- [ ] Use HTTPS (`APP_URL=https://...`)
- [ ] Set `APP_DEBUG=false`
- [ ] Configure proper CORS
- [ ] Enable rate limiting
- [ ] Switch Facebook App to Live Mode
- [ ] Add Privacy Policy URL to Facebook App
- [ ] Add Terms of Service URL to Facebook App
- [ ] Use environment variables for all credentials
- [ ] Enable logging for production monitoring
- [ ] Test the entire flow end-to-end

## Support

Need help? Check these resources:

- [Full Documentation](README.md)
- [Troubleshooting Guide](TROUBLESHOOTING.md)
- [Security Best Practices](SECURITY.md)
- [GitHub Issues](https://github.com/eduardoks98/facebook-auth/issues)

Happy coding!
