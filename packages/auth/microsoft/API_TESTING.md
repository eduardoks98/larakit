# API Testing Guide

Complete guide for testing Microsoft Auth package endpoints with cURL, Postman, and HTTPie.

## Table of Contents

- [Environment Setup](#environment-setup)
- [OAuth Flow Testing](#oauth-flow-testing)
- [Authenticated Endpoints](#authenticated-endpoints)
- [Postman Collection](#postman-collection)
- [Testing Scripts](#testing-scripts)

## Environment Setup

Set these environment variables:

```bash
export API_URL="http://localhost:8000"
export FRONTEND_URL="http://localhost:3000"
export AUTH_TOKEN="" # Will be filled after authentication
```

## OAuth Flow Testing

### 1. Get Authorization URL

**cURL:**
```bash
curl -X GET "${API_URL}/api/auth/microsoft/redirect" \
  -H "Accept: application/json"
```

**HTTPie:**
```bash
http GET "${API_URL}/api/auth/microsoft/redirect" \
  Accept:application/json
```

**Expected Response:**
```json
{
  "authorization_url": "https://login.microsoftonline.com/common/oauth2/v2.0/authorize?...",
  "state": "abc123def456..."
}
```

**Browser Test:**
```
Open in browser: http://localhost:8000/api/auth/microsoft/redirect
```

### 2. OAuth Callback (Automatic)

This endpoint is called by Microsoft after user authorization.

**Manual Test URL:**
```
http://localhost:8000/api/auth/microsoft/callback?code=AUTH_CODE&state=STATE_VALUE
```

**Expected Response (JSON):**
```json
{
  "message": "Authentication successful",
  "token": "1|abc123def456...",
  "user": {
    "id": 1,
    "name": "John Doe",
    "email": "john@example.com",
    "created_at": "2024-01-24T10:00:00.000000Z",
    "updated_at": "2024-01-24T10:00:00.000000Z"
  },
  "microsoft_user": {
    "id": 1,
    "user_id": 1,
    "microsoft_id": "abc-123-def-456",
    "email": "john@example.com",
    "name": "John Doe",
    "user_principal_name": "john@company.com",
    "job_title": "Software Engineer",
    "office_location": "Building 1",
    "last_login_at": "2024-01-24T10:00:00.000000Z"
  }
}
```

**Expected Response (Redirect):**
```
Redirects to: http://localhost:3000/auth/callback?token=1|abc123...&user_id=1
```

## Authenticated Endpoints

Save the token from login response:
```bash
export AUTH_TOKEN="1|abc123def456..."
```

### 3. Get Current User and Microsoft Account

**cURL:**
```bash
curl -X GET "${API_URL}/api/auth/microsoft/me" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer ${AUTH_TOKEN}"
```

**HTTPie:**
```bash
http GET "${API_URL}/api/auth/microsoft/me" \
  Accept:application/json \
  "Authorization: Bearer ${AUTH_TOKEN}"
```

**Expected Response:**
```json
{
  "user": {
    "id": 1,
    "name": "John Doe",
    "email": "john@example.com",
    "email_verified_at": "2024-01-24T10:00:00.000000Z",
    "created_at": "2024-01-24T10:00:00.000000Z",
    "updated_at": "2024-01-24T10:00:00.000000Z"
  },
  "microsoft_user": {
    "id": 1,
    "user_id": 1,
    "microsoft_id": "abc-123-def-456",
    "email": "john@example.com",
    "name": "John Doe",
    "given_name": "John",
    "surname": "Doe",
    "user_principal_name": "john@company.com",
    "job_title": "Software Engineer",
    "office_location": "Building 1",
    "mobile_phone": "+1234567890",
    "business_phones": ["+1234567890"],
    "preferred_language": "en-US",
    "avatar_url": null,
    "tenant_id": "tenant-123",
    "last_login_at": "2024-01-24T10:00:00.000000Z",
    "created_at": "2024-01-24T10:00:00.000000Z",
    "updated_at": "2024-01-24T10:00:00.000000Z"
  }
}
```

### 4. Refresh Microsoft Access Token

**cURL:**
```bash
curl -X POST "${API_URL}/api/auth/microsoft/refresh" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer ${AUTH_TOKEN}"
```

**HTTPie:**
```bash
http POST "${API_URL}/api/auth/microsoft/refresh" \
  Accept:application/json \
  "Authorization: Bearer ${AUTH_TOKEN}"
```

**Expected Response:**
```json
{
  "message": "Token refreshed successfully",
  "expires_at": "2024-01-24T11:00:00.000000Z"
}
```

### 5. Unlink Microsoft Account

**cURL:**
```bash
curl -X POST "${API_URL}/api/auth/microsoft/unlink" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer ${AUTH_TOKEN}"
```

**HTTPie:**
```bash
http POST "${API_URL}/api/auth/microsoft/unlink" \
  Accept:application/json \
  "Authorization: Bearer ${AUTH_TOKEN}"
```

**Expected Response:**
```json
{
  "message": "Microsoft account unlinked successfully"
}
```

## Error Responses

### 401 Unauthorized

```json
{
  "error": "Unauthenticated"
}
```

### 403 Forbidden

```json
{
  "error": "Microsoft account not linked"
}
```

### 404 Not Found

```json
{
  "error": "Microsoft account not linked"
}
```

### 500 Server Error

```json
{
  "error": "Authentication failed",
  "message": "Detailed error message"
}
```

## Postman Collection

### Import to Postman

Create a new collection and add these requests:

#### Collection Variables

```
api_url: http://localhost:8000
auth_token: (empty, will be set after login)
```

#### Request 1: Get Authorization URL

```
GET {{api_url}}/api/auth/microsoft/redirect
Headers:
  Accept: application/json
```

#### Request 2: Get Current Microsoft User

```
GET {{api_url}}/api/auth/microsoft/me
Headers:
  Accept: application/json
  Authorization: Bearer {{auth_token}}
```

#### Request 3: Refresh Token

```
POST {{api_url}}/api/auth/microsoft/refresh
Headers:
  Accept: application/json
  Authorization: Bearer {{auth_token}}
```

#### Request 4: Unlink Account

```
POST {{api_url}}/api/auth/microsoft/unlink
Headers:
  Accept: application/json
  Authorization: Bearer {{auth_token}}
```

### Postman Pre-request Script

Add to collection pre-request script to auto-set token:

```javascript
// Get token from environment or response
const token = pm.environment.get("auth_token");
if (token) {
    pm.request.headers.add({
        key: "Authorization",
        value: `Bearer ${token}`
    });
}
```

### Postman Test Script

Add to callback request to auto-save token:

```javascript
// Save token from response
const response = pm.response.json();
if (response.token) {
    pm.environment.set("auth_token", response.token);
    console.log("Token saved:", response.token);
}
```

## Testing Scripts

### Bash Script: Complete Flow Test

```bash
#!/bin/bash

API_URL="http://localhost:8000"

echo "=== Microsoft Auth Flow Test ==="
echo ""

# Step 1: Get authorization URL
echo "1. Getting authorization URL..."
AUTH_RESPONSE=$(curl -s -X GET "${API_URL}/api/auth/microsoft/redirect" \
  -H "Accept: application/json")

AUTH_URL=$(echo $AUTH_RESPONSE | jq -r '.authorization_url')
STATE=$(echo $AUTH_RESPONSE | jq -r '.state')

echo "Authorization URL: $AUTH_URL"
echo "State: $STATE"
echo ""

# Step 2: Manual - User must visit URL and authorize
echo "2. Please visit the authorization URL in your browser and complete the login"
echo "   After redirect, copy the 'token' parameter from the URL"
echo ""
read -p "Enter the token: " TOKEN

if [ -z "$TOKEN" ]; then
    echo "Error: No token provided"
    exit 1
fi

echo ""
echo "Token received: $TOKEN"
echo ""

# Step 3: Get current user
echo "3. Getting current user info..."
USER_RESPONSE=$(curl -s -X GET "${API_URL}/api/auth/microsoft/me" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer ${TOKEN}")

echo $USER_RESPONSE | jq '.'
echo ""

# Step 4: Refresh token
echo "4. Refreshing Microsoft token..."
REFRESH_RESPONSE=$(curl -s -X POST "${API_URL}/api/auth/microsoft/refresh" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer ${TOKEN}")

echo $REFRESH_RESPONSE | jq '.'
echo ""

echo "=== Test Complete ==="
```

### Python Script: API Client

```python
#!/usr/bin/env python3
import requests
import json
from urllib.parse import urlparse, parse_qs

class MicrosoftAuthClient:
    def __init__(self, api_url):
        self.api_url = api_url
        self.token = None

    def get_authorization_url(self):
        """Get Microsoft authorization URL"""
        response = requests.get(
            f"{self.api_url}/api/auth/microsoft/redirect",
            headers={"Accept": "application/json"}
        )
        response.raise_for_status()
        return response.json()

    def set_token_from_callback_url(self, callback_url):
        """Extract token from callback URL"""
        parsed = urlparse(callback_url)
        params = parse_qs(parsed.query)
        self.token = params.get('token', [None])[0]
        return self.token

    def get_me(self):
        """Get current user and Microsoft account"""
        response = requests.get(
            f"{self.api_url}/api/auth/microsoft/me",
            headers={
                "Accept": "application/json",
                "Authorization": f"Bearer {self.token}"
            }
        )
        response.raise_for_status()
        return response.json()

    def refresh_token(self):
        """Refresh Microsoft access token"""
        response = requests.post(
            f"{self.api_url}/api/auth/microsoft/refresh",
            headers={
                "Accept": "application/json",
                "Authorization": f"Bearer {self.token}"
            }
        )
        response.raise_for_status()
        return response.json()

    def unlink(self):
        """Unlink Microsoft account"""
        response = requests.post(
            f"{self.api_url}/api/auth/microsoft/unlink",
            headers={
                "Accept": "application/json",
                "Authorization": f"Bearer {self.token}"
            }
        )
        response.raise_for_status()
        return response.json()

# Usage example
if __name__ == "__main__":
    client = MicrosoftAuthClient("http://localhost:8000")

    # Get authorization URL
    auth_data = client.get_authorization_url()
    print("Authorization URL:", auth_data['authorization_url'])
    print("\nPlease visit the URL and complete login")

    # After redirect, paste the callback URL
    callback_url = input("\nPaste the callback URL: ")
    token = client.set_token_from_callback_url(callback_url)
    print("Token:", token)

    # Get user info
    user_data = client.get_me()
    print("\nUser data:")
    print(json.dumps(user_data, indent=2))

    # Refresh token
    refresh_data = client.refresh_token()
    print("\nRefresh response:")
    print(json.dumps(refresh_data, indent=2))
```

### Node.js Script: API Client

```javascript
// npm install axios

const axios = require('axios');
const readline = require('readline');

const API_URL = 'http://localhost:8000';
let authToken = null;

const rl = readline.createInterface({
  input: process.stdin,
  output: process.stdout
});

async function getAuthorizationUrl() {
  const response = await axios.get(`${API_URL}/api/auth/microsoft/redirect`, {
    headers: { 'Accept': 'application/json' }
  });
  return response.data;
}

async function getMe(token) {
  const response = await axios.get(`${API_URL}/api/auth/microsoft/me`, {
    headers: {
      'Accept': 'application/json',
      'Authorization': `Bearer ${token}`
    }
  });
  return response.data;
}

async function refreshToken(token) {
  const response = await axios.post(`${API_URL}/api/auth/microsoft/refresh`, {}, {
    headers: {
      'Accept': 'application/json',
      'Authorization': `Bearer ${token}`
    }
  });
  return response.data;
}

async function main() {
  try {
    // Get authorization URL
    console.log('Getting authorization URL...');
    const authData = await getAuthorizationUrl();
    console.log('Authorization URL:', authData.authorization_url);
    console.log('State:', authData.state);

    // Wait for user to complete OAuth flow
    console.log('\nPlease visit the authorization URL and complete login');

    rl.question('\nEnter the token from callback URL: ', async (token) => {
      authToken = token;

      // Get user info
      console.log('\nGetting user info...');
      const userData = await getMe(authToken);
      console.log('User:', JSON.stringify(userData, null, 2));

      // Refresh token
      console.log('\nRefreshing token...');
      const refreshData = await refreshToken(authToken);
      console.log('Refresh response:', JSON.stringify(refreshData, null, 2));

      rl.close();
    });
  } catch (error) {
    console.error('Error:', error.response?.data || error.message);
    rl.close();
  }
}

main();
```

## Integration Testing

### PHPUnit Test

```php
// tests/Feature/MicrosoftAuthApiTest.php
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MicrosoftAuthApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_get_authorization_url()
    {
        $response = $this->getJson('/api/auth/microsoft/redirect');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'authorization_url',
                'state',
            ]);

        $this->assertStringContainsString(
            'login.microsoftonline.com',
            $response->json('authorization_url')
        );
    }

    public function test_me_endpoint_requires_authentication()
    {
        $response = $this->getJson('/api/auth/microsoft/me');

        $response->assertStatus(401);
    }
}
```

## Debugging

### Enable Debug Logging

```php
// config/logging.php
'channels' => [
    'microsoft_auth' => [
        'driver' => 'daily',
        'path' => storage_path('logs/microsoft-auth.log'),
        'level' => 'debug',
        'days' => 14,
    ],
],
```

### Check Logs

```bash
# Laravel logs
tail -f storage/logs/laravel.log

# Microsoft auth specific logs
tail -f storage/logs/microsoft-auth.log
```

### Common Issues

**"Invalid authorization code"**
- Code has expired (10 minutes)
- Code has already been used
- Solution: Get a new authorization URL

**"Invalid state parameter"**
- Session expired
- CSRF mismatch
- Solution: Clear session and retry

**"Token expired and could not be refreshed"**
- Refresh token is invalid or expired
- Solution: User must re-authenticate

## Performance Testing

### Load Test with Apache Bench

```bash
# Test authorization URL endpoint
ab -n 1000 -c 10 http://localhost:8000/api/auth/microsoft/redirect
```

### Load Test with wrk

```bash
# Install wrk: https://github.com/wg/wrk

wrk -t10 -c100 -d30s http://localhost:8000/api/auth/microsoft/redirect
```

## Summary

This guide covers all endpoints and testing methods for the Microsoft Auth package. For production use:

1. Use HTTPS URLs
2. Store tokens securely
3. Implement proper error handling
4. Monitor logs for issues
5. Set up automated testing
6. Test with real Microsoft accounts
7. Verify all scopes work correctly
