# Quick Start Guide

Get Microsoft Auth up and running in 5 minutes.

## Prerequisites

- Laravel 10.x, 11.x, or 12.x
- PHP 8.1 or higher
- Azure account (free tier works)
- Composer

## Step 1: Install Package (30 seconds)

```bash
composer require eduardoks98/microsoft-auth
```

## Step 2: Publish Assets (30 seconds)

```bash
php artisan vendor:publish --tag=microsoft-config
php artisan vendor:publish --tag=microsoft-migrations
php artisan migrate
```

## Step 3: Azure AD Setup (2 minutes)

### Create App Registration

1. Go to [Azure Portal](https://portal.azure.com)
2. Navigate to **Azure Active Directory** → **App registrations** → **New registration**
3. Fill in:
   - **Name**: Your App Name
   - **Account types**: Accounts in any organizational directory and personal Microsoft accounts
   - **Redirect URI**: `http://localhost:8000/api/auth/microsoft/callback` (for local dev)
4. Click **Register**

### Get Credentials

1. Copy **Application (client) ID** from Overview page
2. Go to **Certificates & secrets** → **New client secret**
3. Add description, click **Add**
4. Copy the secret **Value** (you can't see it again!)

## Step 4: Configure Environment (1 minute)

Add to `.env`:

```env
MICROSOFT_CLIENT_ID=paste_your_client_id_here
MICROSOFT_CLIENT_SECRET=paste_your_secret_here
MICROSOFT_TENANT=common
MICROSOFT_REDIRECT_URI=http://localhost:8000/api/auth/microsoft/callback
MICROSOFT_FRONTEND_REDIRECT_URL=http://localhost:3000/auth/callback
```

## Step 5: Test It (1 minute)

### Start Laravel

```bash
php artisan serve
```

### Test in Browser

Open: http://localhost:8000/api/auth/microsoft/redirect

You should be redirected to Microsoft login page!

## Frontend Integration

### React Example

```jsx
// LoginPage.jsx
function LoginPage() {
  const handleLogin = () => {
    window.location.href = 'http://localhost:8000/api/auth/microsoft/redirect';
  };

  return (
    <button onClick={handleLogin}>
      Login with Microsoft
    </button>
  );
}

// CallbackPage.jsx
function CallbackPage() {
  useEffect(() => {
    const params = new URLSearchParams(window.location.search);
    const token = params.get('token');

    if (token) {
      localStorage.setItem('auth_token', token);
      window.location.href = '/dashboard';
    }
  }, []);

  return <div>Processing...</div>;
}
```

### Vue Example

```vue
<!-- Login.vue -->
<template>
  <button @click="loginWithMicrosoft">
    Login with Microsoft
  </button>
</template>

<script>
export default {
  methods: {
    loginWithMicrosoft() {
      window.location.href = 'http://localhost:8000/api/auth/microsoft/redirect';
    }
  }
}
</script>

<!-- Callback.vue -->
<template>
  <div>Processing...</div>
</template>

<script>
export default {
  mounted() {
    const params = new URLSearchParams(window.location.search);
    const token = params.get('token');

    if (token) {
      localStorage.setItem('auth_token', token);
      this.$router.push('/dashboard');
    }
  }
}
</script>
```

## Test API Endpoints

### Get User Info

```bash
# Save token from callback
TOKEN="your_token_here"

# Get current user
curl -H "Authorization: Bearer $TOKEN" \
     http://localhost:8000/api/auth/microsoft/me
```

### Response

```json
{
  "user": {
    "id": 1,
    "name": "John Doe",
    "email": "john@example.com"
  },
  "microsoft_user": {
    "email": "john@example.com",
    "name": "John Doe",
    "user_principal_name": "john@company.com",
    "job_title": "Software Engineer"
  }
}
```

## Common Issues

### Redirect URI Mismatch

**Error**: `AADSTS50011: Redirect URI mismatch`

**Fix**: Ensure redirect URI in Azure AD matches `.env` exactly:
- Azure AD: `http://localhost:8000/api/auth/microsoft/callback`
- .env: `MICROSOFT_REDIRECT_URI=http://localhost:8000/api/auth/microsoft/callback`

### Invalid Client Secret

**Error**: `AADSTS7000215: Invalid client secret`

**Fix**:
1. Create new secret in Azure AD
2. Copy immediately
3. Update `.env` file
4. Restart Laravel server

### Session Not Working

**Error**: `Invalid state parameter`

**Fix**: Ensure session is configured:
```env
SESSION_DRIVER=file  # or redis, database, etc.
```

## Next Steps

1. **Production Setup**
   - Use HTTPS
   - Update redirect URIs in Azure AD
   - Set production environment variables

2. **Customize**
   - See [config/microsoft.php](config/microsoft.php) for all options
   - Read [USAGE_EXAMPLES.md](USAGE_EXAMPLES.md) for advanced usage

3. **Microsoft Graph API**
   - Add more scopes (Mail.Read, Calendars.Read, etc.)
   - See [USAGE_EXAMPLES.md](USAGE_EXAMPLES.md) for Graph API examples

4. **Testing**
   - Read [API_TESTING.md](API_TESTING.md) for testing guide
   - Run `php artisan test` to verify installation

## Full Documentation

- [README.md](README.md) - Complete documentation
- [AZURE_AD_SETUP.md](AZURE_AD_SETUP.md) - Detailed Azure setup
- [USAGE_EXAMPLES.md](USAGE_EXAMPLES.md) - Code examples
- [INTEGRATION_GUIDE.md](INTEGRATION_GUIDE.md) - Integration with existing apps
- [FAQ.md](FAQ.md) - Frequently asked questions

## Support

- Issues: GitHub Issues
- Questions: FAQ.md
- Examples: USAGE_EXAMPLES.md

## Minimal Working Example

Complete working example in 50 lines:

### Backend (Laravel)

Package is already configured! Just set `.env` variables.

### Frontend (HTML + Vanilla JS)

```html
<!DOCTYPE html>
<html>
<head>
    <title>Microsoft Auth Demo</title>
</head>
<body>
    <div id="app">
        <div id="login-section">
            <h1>Login</h1>
            <button onclick="login()">Login with Microsoft</button>
        </div>

        <div id="user-section" style="display: none;">
            <h1>Welcome, <span id="user-name"></span>!</h1>
            <p>Email: <span id="user-email"></span></p>
            <button onclick="logout()">Logout</button>
        </div>
    </div>

    <script>
        const API_URL = 'http://localhost:8000';

        // Check if we're on callback page
        window.onload = function() {
            const params = new URLSearchParams(window.location.search);
            const token = params.get('token');

            if (token) {
                localStorage.setItem('auth_token', token);
                window.location.href = '/';
                return;
            }

            checkAuth();
        };

        function login() {
            window.location.href = API_URL + '/api/auth/microsoft/redirect';
        }

        async function checkAuth() {
            const token = localStorage.getItem('auth_token');
            if (!token) return;

            try {
                const response = await fetch(API_URL + '/api/auth/microsoft/me', {
                    headers: {
                        'Authorization': 'Bearer ' + token,
                        'Accept': 'application/json'
                    }
                });

                if (!response.ok) throw new Error('Not authenticated');

                const data = await response.json();
                showUser(data.user);
            } catch (error) {
                console.error(error);
                localStorage.removeItem('auth_token');
            }
        }

        function showUser(user) {
            document.getElementById('login-section').style.display = 'none';
            document.getElementById('user-section').style.display = 'block';
            document.getElementById('user-name').textContent = user.name;
            document.getElementById('user-email').textContent = user.email;
        }

        function logout() {
            localStorage.removeItem('auth_token');
            location.reload();
        }
    </script>
</body>
</html>
```

Save as `index.html` and open in browser!

## That's It!

You now have a working Microsoft OAuth authentication system.

For advanced features, read the complete documentation.

**Happy coding!**
