# Google Auth API Documentation

Complete API documentation for the Google Auth package endpoints.

## Base URL

```
http://localhost:8000/api/auth/google
```

## Authentication

Most endpoints require authentication using Laravel Sanctum tokens. Include the token in the Authorization header:

```
Authorization: Bearer YOUR_TOKEN_HERE
```

---

## Endpoints

### 1. Get Authorization URL

Generates the Google OAuth 2.0 authorization URL.

**Endpoint:** `GET /redirect`

**Authentication:** Not required

**Query Parameters:** None

**Headers:**
```
Accept: application/json
```

**Success Response (200):**
```json
{
  "success": true,
  "message": "Authorization URL generated successfully",
  "data": {
    "authorization_url": "https://accounts.google.com/o/oauth2/auth?...",
    "state": "random-state-string-for-csrf-protection"
  }
}
```

**Error Response (500):**
```json
{
  "success": false,
  "message": "Failed to generate authorization URL",
  "error": "Error details here"
}
```

**Example Request:**
```bash
curl -X GET http://localhost:8000/api/auth/google/redirect \
  -H "Accept: application/json"
```

**Browser Redirect:**

If the request does not expect JSON (browser request), it will redirect directly to Google's OAuth page:
```
HTTP/1.1 302 Found
Location: https://accounts.google.com/o/oauth2/auth?...
```

**Usage Flow:**
1. Frontend calls this endpoint
2. Receives the authorization URL
3. Redirects user to the URL
4. User authorizes on Google
5. Google redirects back to callback endpoint

---

### 2. Handle OAuth Callback

Processes the OAuth callback from Google and authenticates the user.

**Endpoint:** `GET /callback`

**Authentication:** Not required

**Query Parameters:**

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| code | string | Yes | Authorization code from Google |
| state | string | Yes | State parameter for CSRF protection |
| error | string | No | Error code if authorization failed |
| error_description | string | No | Human-readable error description |

**Headers:**
```
Accept: application/json
```

**Success Response (200):**
```json
{
  "success": true,
  "message": "Authentication successful",
  "data": {
    "user": {
      "id": 1,
      "name": "John Doe",
      "email": "john@example.com",
      "email_verified_at": "2024-01-24T10:00:00.000000Z",
      "created_at": "2024-01-24T10:00:00.000000Z",
      "updated_at": "2024-01-24T10:00:00.000000Z"
    },
    "google_user": {
      "id": "9a8b7c6d-5e4f-3a2b-1c0d-9e8f7a6b5c4d",
      "user_id": 1,
      "google_id": "123456789012345678901",
      "email": "john@example.com",
      "name": "John Doe",
      "given_name": "John",
      "family_name": "Doe",
      "picture": "https://lh3.googleusercontent.com/...",
      "locale": "en",
      "token_type": "Bearer",
      "last_login_at": "2024-01-24T10:00:00.000000Z",
      "created_at": "2024-01-24T10:00:00.000000Z",
      "updated_at": "2024-01-24T10:00:00.000000Z"
    },
    "token": "1|AbCdEfGhIjKlMnOpQrStUvWxYz1234567890",
    "token_type": "Bearer"
  }
}
```

**Error Response - Authorization Denied (400):**
```json
{
  "success": false,
  "message": "User denied authorization",
  "error": "access_denied"
}
```

**Error Response - Invalid State (400):**
```json
{
  "success": false,
  "message": "Invalid state parameter"
}
```

**Error Response - Missing Code (400):**
```json
{
  "success": false,
  "message": "Authorization code not provided"
}
```

**Error Response - Google API Error (401):**
```json
{
  "success": false,
  "message": "Failed to authenticate with Google: Invalid authorization code",
  "error": "invalid_grant",
  "response_body": {
    "error": "invalid_grant",
    "error_description": "Bad Request"
  }
}
```

**Error Response - General Error (500):**
```json
{
  "success": false,
  "message": "Authentication failed: Error details",
  "error": "Error message"
}
```

**Example Request:**
```bash
curl -X GET "http://localhost:8000/api/auth/google/callback?code=4/0AY0e-g7xyz...&state=random-state" \
  -H "Accept: application/json"
```

**Browser Redirect:**

If the request does not expect JSON, it will redirect to the frontend URL with the token or error:

Success:
```
HTTP/1.1 302 Found
Location: http://localhost:3000/auth/callback?token=1|AbCdEf...
```

Error:
```
HTTP/1.1 302 Found
Location: http://localhost:3000/auth/callback?error=Authentication+failed
```

**Notes:**
- This endpoint is automatically called by Google after user authorization
- The state parameter is validated against the session to prevent CSRF attacks
- If `GOOGLE_AUTO_CREATE_USERS` is true, new users are created automatically
- If `GOOGLE_AUTO_SYNC_USER_DATA` is true, user data is synced from Google
- A Sanctum token is generated and returned for API authentication

---

### 3. Get Google Profile

Retrieves the authenticated user's Google profile information.

**Endpoint:** `GET /profile`

**Authentication:** Required (Sanctum token)

**Query Parameters:** None

**Headers:**
```
Accept: application/json
Authorization: Bearer YOUR_TOKEN
```

**Success Response (200):**
```json
{
  "success": true,
  "message": "Google profile retrieved successfully",
  "data": {
    "google_user": {
      "id": "9a8b7c6d-5e4f-3a2b-1c0d-9e8f7a6b5c4d",
      "user_id": 1,
      "google_id": "123456789012345678901",
      "email": "john@example.com",
      "name": "John Doe",
      "given_name": "John",
      "family_name": "Doe",
      "picture": "https://lh3.googleusercontent.com/...",
      "locale": "en",
      "access_token": "ya29.a0AfH6SMBx...",
      "refresh_token": "1//0gZ1qX...",
      "expires_in": 3600,
      "token_type": "Bearer",
      "last_login_at": "2024-01-24T10:00:00.000000Z",
      "created_at": "2024-01-24T10:00:00.000000Z",
      "updated_at": "2024-01-24T10:00:00.000000Z"
    },
    "is_token_expired": false
  }
}
```

**Error Response - Not Linked (404):**
```json
{
  "success": false,
  "message": "Google account not linked to this user"
}
```

**Error Response - Unauthenticated (401):**
```json
{
  "message": "Unauthenticated."
}
```

**Error Response - General Error (500):**
```json
{
  "success": false,
  "message": "Failed to retrieve Google profile",
  "error": "Error details"
}
```

**Example Request:**
```bash
curl -X GET http://localhost:8000/api/auth/google/profile \
  -H "Accept: application/json" \
  -H "Authorization: Bearer 1|AbCdEf..."
```

**Notes:**
- Returns the GoogleUser model with access_token and refresh_token visible
- Includes `is_token_expired` boolean to check if the token needs refreshing
- Requires the user to be authenticated with a Sanctum token

---

### 4. Refresh Access Token

Refreshes the Google access token using the stored refresh token.

**Endpoint:** `POST /refresh`

**Authentication:** Required (Sanctum token)

**Request Body:** None

**Headers:**
```
Accept: application/json
Authorization: Bearer YOUR_TOKEN
```

**Success Response (200):**
```json
{
  "success": true,
  "message": "Token refreshed successfully",
  "data": {
    "access_token": "ya29.a0AfH6SMBx...",
    "expires_in": 3600
  }
}
```

**Error Response - Not Linked (404):**
```json
{
  "success": false,
  "message": "Google account not linked to this user"
}
```

**Error Response - No Refresh Token (500):**
```json
{
  "success": false,
  "message": "Failed to refresh token",
  "error": "No refresh token available. User must re-authenticate."
}
```

**Error Response - Invalid Refresh Token (500):**
```json
{
  "success": false,
  "message": "Failed to refresh token",
  "error": "Invalid refresh token"
}
```

**Example Request:**
```bash
curl -X POST http://localhost:8000/api/auth/google/refresh \
  -H "Accept: application/json" \
  -H "Authorization: Bearer 1|AbCdEf..."
```

**Notes:**
- Automatically refreshes the token if expired
- Updates the GoogleUser record with the new access token
- Returns the new access token and expiration time
- Requires `GOOGLE_ENABLE_REFRESH_TOKEN=true` to have refresh tokens

---

### 5. Revoke Google Access

Revokes the user's Google access and unlinks the Google account.

**Endpoint:** `DELETE /revoke`

**Authentication:** Required (Sanctum token)

**Request Body:** None

**Headers:**
```
Accept: application/json
Authorization: Bearer YOUR_TOKEN
```

**Success Response (200):**
```json
{
  "success": true,
  "message": "Google access revoked successfully",
  "data": null
}
```

**Error Response - Not Linked (404):**
```json
{
  "success": false,
  "message": "Google account not linked to this user"
}
```

**Error Response - General Error (500):**
```json
{
  "success": false,
  "message": "Failed to revoke Google access",
  "error": "Error details"
}
```

**Example Request:**
```bash
curl -X DELETE http://localhost:8000/api/auth/google/revoke \
  -H "Accept: application/json" \
  -H "Authorization: Bearer 1|AbCdEf..."
```

**Notes:**
- Revokes the token with Google's API
- Deletes the GoogleUser record from the database
- Does not delete the User account, only unlinks Google
- Even if Google revocation fails, the local record is deleted
- Errors are logged but not returned to the user

---

## Error Handling

All endpoints follow a consistent error response format:

```json
{
  "success": false,
  "message": "Human-readable error message",
  "error": "Technical error details (optional)",
  "data": null
}
```

### HTTP Status Codes

| Code | Meaning | When It Occurs |
|------|---------|----------------|
| 200 | OK | Request successful |
| 302 | Found | Redirect to Google or frontend |
| 400 | Bad Request | Invalid parameters or state mismatch |
| 401 | Unauthorized | Invalid or expired token |
| 404 | Not Found | Google account not linked |
| 500 | Internal Server Error | Server-side error or Google API error |

---

## Authentication Flow

```
┌─────────┐                ┌──────────┐                ┌────────┐
│ Frontend│                │  Laravel │                │ Google │
└────┬────┘                └────┬─────┘                └───┬────┘
     │                          │                          │
     │  GET /redirect           │                          │
     ├─────────────────────────>│                          │
     │                          │                          │
     │  Authorization URL       │                          │
     │<─────────────────────────┤                          │
     │                          │                          │
     │  Redirect to Google      │                          │
     ├──────────────────────────┼─────────────────────────>│
     │                          │                          │
     │                          │  User Authorizes         │
     │                          │                          │
     │  Redirect with code      │                          │
     │<─────────────────────────┼──────────────────────────┤
     │                          │                          │
     │  GET /callback?code=xxx  │                          │
     ├─────────────────────────>│                          │
     │                          │                          │
     │                          │  Exchange code for token │
     │                          ├─────────────────────────>│
     │                          │                          │
     │                          │  Access Token + User Info│
     │                          │<─────────────────────────┤
     │                          │                          │
     │  Sanctum Token           │                          │
     │<─────────────────────────┤                          │
     │                          │                          │
     │  API Requests            │                          │
     │  (with Sanctum token)    │                          │
     ├─────────────────────────>│                          │
     │                          │                          │
```

---

## Rate Limiting

Consider implementing rate limiting for the public endpoints:

```php
Route::middleware(['throttle:10,1'])->group(function () {
    Route::get('/redirect', [GoogleAuthController::class, 'redirect']);
    Route::get('/callback', [GoogleAuthController::class, 'callback']);
});
```

---

## CORS Configuration

Ensure your `config/cors.php` allows requests from your frontend:

```php
'allowed_origins' => [
    'http://localhost:3000',
    'https://yourdomain.com',
],
'supports_credentials' => true,
```

---

## Testing

### Postman Collection

Import the provided Postman collection from `examples/postman-collection.json`.

### Manual Testing

1. Get authorization URL:
   ```bash
   curl -X GET http://localhost:8000/api/auth/google/redirect \
     -H "Accept: application/json"
   ```

2. Open the URL in a browser and authorize

3. Copy the callback URL with code and state

4. Test callback:
   ```bash
   curl -X GET "http://localhost:8000/api/auth/google/callback?code=xxx&state=xxx" \
     -H "Accept: application/json"
   ```

5. Use the returned token for authenticated requests:
   ```bash
   curl -X GET http://localhost:8000/api/auth/google/profile \
     -H "Authorization: Bearer YOUR_TOKEN" \
     -H "Accept: application/json"
   ```

---

## Additional Information

### Token Structure

The Sanctum token returned is in the format:
```
{token_id}|{plain_text_token}
```

Example:
```
1|AbCdEfGhIjKlMnOpQrStUvWxYz1234567890
```

### Google User Data

The Google API returns the following user data:

```json
{
  "sub": "123456789012345678901",
  "name": "John Doe",
  "given_name": "John",
  "family_name": "Doe",
  "picture": "https://lh3.googleusercontent.com/...",
  "email": "john@example.com",
  "email_verified": true,
  "locale": "en"
}
```

### Token Expiration

- Access tokens typically expire after 1 hour (3600 seconds)
- Refresh tokens can be used to obtain new access tokens
- Set `GOOGLE_ACCESS_TYPE=offline` to receive refresh tokens
- Use the `/refresh` endpoint to get new access tokens

### Security Considerations

1. **CSRF Protection**: State parameter validates requests
2. **HTTPS**: Always use HTTPS in production
3. **Token Storage**: Store tokens securely (HttpOnly cookies or secure storage)
4. **Scope Limitation**: Only request necessary OAuth scopes
5. **Token Validation**: Sanctum validates tokens on each request

---

## Support

For issues and questions:
- Check the [README](README.md)
- Review the [Setup Guide](SETUP_GUIDE.md)
- See [Frontend Integration Examples](examples/frontend-integration.md)
- Open an issue on GitHub
