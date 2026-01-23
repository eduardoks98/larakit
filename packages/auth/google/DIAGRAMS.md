# Google Auth Package - System Diagrams

This document contains visual diagrams to help understand the package architecture and flows.

## Table of Contents

1. [System Architecture](#system-architecture)
2. [OAuth Flow Diagram](#oauth-flow-diagram)
3. [Database Schema](#database-schema)
4. [Component Interaction](#component-interaction)
5. [Token Lifecycle](#token-lifecycle)
6. [Error Handling Flow](#error-handling-flow)

---

## System Architecture

```
┌─────────────────────────────────────────────────────────────────────┐
│                         FRONTEND LAYER                              │
│  ┌────────────┐  ┌────────────┐  ┌────────────┐  ┌────────────┐   │
│  │   React    │  │   Vue.js   │  │  Angular   │  │  Vanilla   │   │
│  │ Components │  │ Components │  │ Components │  │     JS     │   │
│  └─────┬──────┘  └─────┬──────┘  └─────┬──────┘  └─────┬──────┘   │
│        │               │                │                │           │
│        └───────────────┴────────────────┴────────────────┘           │
│                              │                                       │
│                    HTTP/HTTPS Requests                               │
└──────────────────────────────┼──────────────────────────────────────┘
                               │
┌──────────────────────────────▼──────────────────────────────────────┐
│                      LARAVEL APPLICATION                             │
│  ┌────────────────────────────────────────────────────────────────┐ │
│  │                      ROUTE LAYER                                │ │
│  │  /api/auth/google/redirect                                      │ │
│  │  /api/auth/google/callback                                      │ │
│  │  /api/auth/google/profile  (auth:sanctum)                       │ │
│  │  /api/auth/google/refresh  (auth:sanctum)                       │ │
│  │  /api/auth/google/revoke   (auth:sanctum)                       │ │
│  └───────────────────────────┬────────────────────────────────────┘ │
│                              │                                       │
│  ┌───────────────────────────▼────────────────────────────────────┐ │
│  │              CONTROLLER LAYER                                   │ │
│  │  GoogleAuthController                                           │ │
│  │  - redirect()                                                   │ │
│  │  - callback()                                                   │ │
│  │  - profile()                                                    │ │
│  │  - refresh()                                                    │ │
│  │  - revoke()                                                     │ │
│  └───────────────────────────┬────────────────────────────────────┘ │
│                              │                                       │
│  ┌───────────────────────────▼────────────────────────────────────┐ │
│  │               SERVICE LAYER                                     │ │
│  │  GoogleAuthService                                              │ │
│  │  - getAuthorizationUrl()                                        │ │
│  │  - getAccessToken()                                             │ │
│  │  - refreshAccessToken()                                         │ │
│  │  - getResourceOwner()                                           │ │
│  │  - handleCallback()                                             │ │
│  │  - revokeAccess()                                               │ │
│  │  - getValidAccessToken()                                        │ │
│  └───┬────────────────────────┬───────────────────────────────────┘ │
│      │                        │                                      │
│      │                        │                                      │
│  ┌───▼────────────┐   ┌───────▼──────────┐                         │
│  │  OAuth2 Client │   │  MODEL LAYER     │                         │
│  │  (League)      │   │  - GoogleUser    │                         │
│  │                │   │  - User          │                         │
│  └────────┬───────┘   └───────┬──────────┘                         │
│           │                   │                                      │
└───────────┼───────────────────┼──────────────────────────────────────┘
            │                   │
            │              ┌────▼──────┐
            │              │  Database │
            │              │  - users  │
            │              │  - google_│
            │              │    users  │
            │              └───────────┘
            │
┌───────────▼──────────────────────────────────────────────────────────┐
│                       GOOGLE OAUTH 2.0 API                           │
│  - accounts.google.com/o/oauth2/auth                                 │
│  - oauth2.googleapis.com/token                                       │
│  - www.googleapis.com/oauth2/v3/userinfo                             │
│  - oauth2.googleapis.com/revoke                                      │
└──────────────────────────────────────────────────────────────────────┘
```

---

## OAuth Flow Diagram

### Complete Authentication Sequence

```
┌────────┐         ┌──────────┐         ┌─────────┐         ┌────────┐
│ User   │         │ Frontend │         │ Laravel │         │ Google │
└───┬────┘         └────┬─────┘         └────┬────┘         └───┬────┘
    │                   │                    │                   │
    │ 1. Click Login    │                    │                   │
    ├──────────────────>│                    │                   │
    │                   │                    │                   │
    │                   │ 2. GET /redirect   │                   │
    │                   ├───────────────────>│                   │
    │                   │                    │                   │
    │                   │ 3. Auth URL        │                   │
    │                   │<───────────────────┤                   │
    │                   │   + state          │                   │
    │                   │                    │                   │
    │ 4. Redirect to Google                  │                   │
    ├────────────────────────────────────────┼──────────────────>│
    │                   │                    │                   │
    │                   │                    │ 5. Show consent   │
    │<──────────────────────────────────────────────────────────┤
    │                   │                    │                   │
    │ 6. User approves  │                    │                   │
    ├──────────────────────────────────────────────────────────>│
    │                   │                    │                   │
    │ 7. Redirect with code + state          │                   │
    │<───────────────────┼────────────────────┼───────────────────┤
    │                   │                    │                   │
    │ 8. GET /callback  │                    │                   │
    │   ?code=xxx       │                    │                   │
    │   &state=xxx      │                    │                   │
    ├──────────────────>├───────────────────>│                   │
    │                   │                    │                   │
    │                   │                    │ 9. Validate state │
    │                   │                    │ ───┐              │
    │                   │                    │ <──┘              │
    │                   │                    │                   │
    │                   │                    │ 10. Exchange code │
    │                   │                    │    for token      │
    │                   │                    ├──────────────────>│
    │                   │                    │                   │
    │                   │                    │ 11. Access Token  │
    │                   │                    │    + Refresh Token│
    │                   │                    │<──────────────────┤
    │                   │                    │                   │
    │                   │                    │ 12. Get user info │
    │                   │                    ├──────────────────>│
    │                   │                    │                   │
    │                   │                    │ 13. User data     │
    │                   │                    │<──────────────────┤
    │                   │                    │                   │
    │                   │                    │ 14. Create/Update │
    │                   │                    │     GoogleUser    │
    │                   │                    │ ───┐              │
    │                   │                    │ <──┘              │
    │                   │                    │                   │
    │                   │                    │ 15. Find/Create   │
    │                   │                    │     User          │
    │                   │                    │ ───┐              │
    │                   │                    │ <──┘              │
    │                   │                    │                   │
    │                   │                    │ 16. Create Sanctum│
    │                   │                    │     Token         │
    │                   │                    │ ───┐              │
    │                   │                    │ <──┘              │
    │                   │                    │                   │
    │                   │ 17. Redirect with  │                   │
    │                   │     token          │                   │
    │<──────────────────┼────────────────────┤                   │
    │                   │                    │                   │
    │ 18. Store token   │                    │                   │
    │    in storage     │                    │                   │
    ├──────────────────>│                    │                   │
    │                   │                    │                   │
    │                   │ 19. GET /user      │                   │
    │                   │    Bearer token    │                   │
    │                   ├───────────────────>│                   │
    │                   │                    │                   │
    │                   │ 20. User data      │                   │
    │                   │<───────────────────┤                   │
    │                   │                    │                   │
    │ 21. Show          │                    │                   │
    │     Dashboard     │                    │                   │
    │<──────────────────┤                    │                   │
    │                   │                    │                   │
```

---

## Database Schema

### Entity Relationship Diagram

```
┌─────────────────────────────────┐
│           users                 │
├─────────────────────────────────┤
│ id              BIGINT (PK)     │
│ name            VARCHAR         │
│ email           VARCHAR UNIQUE  │
│ email_verified  TIMESTAMP NULL  │
│ password        VARCHAR NULL    │
│ remember_token  VARCHAR NULL    │
│ created_at      TIMESTAMP       │
│ updated_at      TIMESTAMP       │
└───────────────┬─────────────────┘
                │
                │ 1:1 (HasOne)
                │
┌───────────────▼─────────────────┐
│        google_users             │
├─────────────────────────────────┤
│ id              UUID (PK)       │
│ user_id         BIGINT FK NULL  │◄─── Foreign Key
│ google_id       VARCHAR UNIQUE  │
│ email           VARCHAR INDEX   │
│ name            VARCHAR         │
│ given_name      VARCHAR         │
│ family_name     VARCHAR         │
│ picture         TEXT            │
│ locale          VARCHAR(10)     │
│ access_token    TEXT (HIDDEN)   │
│ refresh_token   TEXT (HIDDEN)   │
│ expires_in      INTEGER         │
│ token_type      VARCHAR         │
│ last_login_at   TIMESTAMP       │
│ created_at      TIMESTAMP       │
│ updated_at      TIMESTAMP       │
│                                 │
│ INDEX: (user_id, google_id)     │
└─────────────────────────────────┘

Relationships:
- User hasOne GoogleUser (user_id)
- GoogleUser belongsTo User (user_id)
- Cascade delete: When User deleted, GoogleUser deleted
```

### Data Flow

```
Google API Response          GoogleUser Table           User Table
┌──────────────────┐        ┌──────────────┐         ┌──────────┐
│ sub: "123456"    ├───────>│ google_id    │         │          │
│ email: "..."     ├───────>│ email        │         │          │
│ name: "..."      ├───────>│ name         │    ┌───>│ name     │
│ given_name: "..."├───────>│ given_name   │    │    │ email    │
│ family_name: "..."├──────>│ family_name  │    │    │ password │
│ picture: "..."   ├───────>│ picture      │    │    └──────────┘
│ locale: "..."    ├───────>│ locale       │    │         △
└──────────────────┘        │              │    │         │
                            │ user_id      ├────┘   Foreign Key
OAuth Token Response        │ access_token │
┌──────────────────┐        │ refresh_token│
│ access_token     ├───────>│ expires_in   │
│ refresh_token    │        │ token_type   │
│ expires_in       │        └──────────────┘
│ token_type       │
└──────────────────┘
```

---

## Component Interaction

### Service Layer Interaction

```
                        GoogleAuthService
    ┌──────────────────────────────────────────────────────┐
    │                                                      │
    │  ┌────────────────────────────────────────────┐    │
    │  │         League OAuth2 Provider              │    │
    │  │  ┌──────────────────────────────────────┐  │    │
    │  │  │  getAuthorizationUrl()                │  │    │
    │  │  │  getAccessToken($code)                │  │    │
    │  │  │  getResourceOwner($token)             │  │    │
    │  │  │  refreshAccessToken($refreshToken)    │  │    │
    │  │  └──────────────────────────────────────┘  │    │
    │  └────────────────────────────────────────────┘    │
    │                                                      │
    │  ┌────────────────────────────────────────────┐    │
    │  │        Business Logic Methods               │    │
    │  │  ┌──────────────────────────────────────┐  │    │
    │  │  │  handleCallback($code)                │  │    │
    │  │  │  findOrCreateGoogleUser()             │  │    │
    │  │  │  findOrCreateUser()                   │  │    │
    │  │  │  syncUserData()                       │  │    │
    │  │  │  revokeAccess()                       │  │    │
    │  │  │  getValidAccessToken()                │  │    │
    │  │  └──────────────────────────────────────┘  │    │
    │  └────────────────────────────────────────────┘    │
    │                                                      │
    │  ┌────────────────────────────────────────────┐    │
    │  │          Model Interactions                 │    │
    │  │  ┌──────────────────────────────────────┐  │    │
    │  │  │  GoogleUser::create()                 │  │    │
    │  │  │  GoogleUser::update()                 │  │    │
    │  │  │  User::create()                       │  │    │
    │  │  │  User::createToken()                  │  │    │
    │  │  └──────────────────────────────────────┘  │    │
    │  └────────────────────────────────────────────┘    │
    │                                                      │
    └──────────────────────────────────────────────────────┘
                            △
                            │
                            │ uses
                            │
    ┌───────────────────────┴───────────────────────────┐
    │           GoogleAuthController                    │
    │  ┌────────────────────────────────────────────┐  │
    │  │  redirect()  ──> getAuthorizationUrl()     │  │
    │  │  callback()  ──> handleCallback()          │  │
    │  │  profile()   ──> user->googleUser          │  │
    │  │  refresh()   ──> getValidAccessToken()     │  │
    │  │  revoke()    ──> revokeAccess()            │  │
    │  └────────────────────────────────────────────┘  │
    └───────────────────────────────────────────────────┘
```

---

## Token Lifecycle

### Token States and Transitions

```
┌─────────────────────────────────────────────────────────────────┐
│                      TOKEN LIFECYCLE                            │
└─────────────────────────────────────────────────────────────────┘

    [Initial State]
          │
          │ User authorizes
          │
          ▼
    ┌──────────────┐
    │ Google       │
    │ generates    │
    │ tokens       │
    └──────┬───────┘
           │
           │ access_token (1 hour TTL)
           │ refresh_token (long-lived)
           │
           ▼
    ┌──────────────┐
    │ Tokens       │
    │ stored in    │
    │ GoogleUser   │
    └──────┬───────┘
           │
           │
     ┌─────┴─────┐
     │           │
     ▼           ▼
┌─────────┐  ┌─────────────┐
│ Token   │  │ Token       │
│ Valid   │  │ Expired     │
└────┬────┘  └──────┬──────┘
     │              │
     │              │ Check expires_in
     │              │ + updated_at
     │              │
     │              ▼
     │        ┌──────────────┐
     │        │ Needs        │
     │        │ Refresh      │
     │        └──────┬───────┘
     │               │
     │               │ Call refresh endpoint
     │               │
     │               ▼
     │        ┌──────────────┐
     │        │ Use refresh  │
     │        │ token to get │
     │        │ new access   │
     │        │ token        │
     │        └──────┬───────┘
     │               │
     │               │
     └───────┬───────┘
             │
             │ New access_token
             │
             ▼
    ┌─────────────────┐
    │ Update          │
    │ GoogleUser      │
    │ with new token  │
    └────────┬────────┘
             │
             │
             ▼
    ┌─────────────────┐
    │ Token Valid     │
    │ for 1 hour      │
    └─────────────────┘

    [Revoke Flow]
             │
             │ User revokes
             │
             ▼
    ┌─────────────────┐
    │ Call Google     │
    │ revoke API      │
    └────────┬────────┘
             │
             ▼
    ┌─────────────────┐
    │ Delete          │
    │ GoogleUser      │
    │ record          │
    └─────────────────┘
             │
             ▼
      [Token Revoked]
```

---

## Error Handling Flow

### Error Handling Decision Tree

```
                    [API Request]
                         │
                         ▼
              ┌──────────────────┐
              │ Validate Request │
              └────────┬─────────┘
                       │
              ┌────────┴────────┐
              │                 │
         ┌────▼────┐      ┌─────▼────┐
         │ Valid   │      │ Invalid  │
         └────┬────┘      └─────┬────┘
              │                 │
              │                 ▼
              │          ┌──────────────┐
              │          │ Return 400   │
              │          │ Bad Request  │
              │          └──────────────┘
              │
              ▼
    ┌─────────────────┐
    │ Execute Logic   │
    └────────┬────────┘
             │
    ┌────────┴────────┐
    │                 │
┌───▼────┐      ┌─────▼─────┐
│Success │      │ Exception │
└───┬────┘      └─────┬─────┘
    │                 │
    │                 ▼
    │         ┌───────────────┐
    │         │ Classify Error│
    │         └───────┬───────┘
    │                 │
    │     ┌───────────┼───────────┐
    │     │           │           │
    │  ┌──▼──┐   ┌────▼───┐  ┌───▼────┐
    │  │OAuth│   │Google  │  │General │
    │  │Error│   │API Err │  │Error   │
    │  └──┬──┘   └────┬───┘  └───┬────┘
    │     │           │           │
    │     │           │           │
    │     ▼           ▼           ▼
    │  ┌──────┐   ┌──────┐   ┌──────┐
    │  │ 401  │   │ 401  │   │ 500  │
    │  │Unauth│   │Unauth│   │Server│
    │  └──────┘   └──────┘   └──────┘
    │
    ▼
┌────────────┐
│ Return 200 │
│ Success    │
└────────────┘

[Response Format]

Success:                      Error:
┌──────────────────┐         ┌──────────────────┐
│ {                │         │ {                │
│   success: true  │         │   success: false │
│   message: "..." │         │   message: "..." │
│   data: {...}    │         │   error: "..."   │
│ }                │         │ }                │
└──────────────────┘         └──────────────────┘
```

---

## Request/Response Flow

### Successful Callback Flow

```
┌──────────────────────────────────────────────────────────────────┐
│                    CALLBACK REQUEST                              │
│  GET /api/auth/google/callback?code=xxx&state=xxx                │
└───────────────────────────┬──────────────────────────────────────┘
                            │
                            ▼
                  ┌─────────────────┐
                  │ Validate State  │
                  └────────┬────────┘
                           │ ✓
                           ▼
                  ┌─────────────────┐
                  │ Exchange Code   │
                  │ for Token       │
                  └────────┬────────┘
                           │
                           ▼
                  ┌─────────────────┐
                  │ Get User Info   │
                  │ from Google     │
                  └────────┬────────┘
                           │
              ┌────────────┴────────────┐
              │                         │
              ▼                         ▼
    ┌─────────────────┐      ┌─────────────────┐
    │ Find/Create     │      │ Find/Create     │
    │ GoogleUser      │      │ User            │
    └────────┬────────┘      └────────┬────────┘
             │                        │
             │                        │
             │        Link            │
             └───────────┬────────────┘
                         │
                         ▼
                ┌────────────────┐
                │ Create Sanctum │
                │ Token          │
                └────────┬───────┘
                         │
                         ▼
┌────────────────────────────────────────────────────────────────┐
│                    SUCCESS RESPONSE                            │
│  {                                                             │
│    success: true,                                              │
│    message: "Authentication successful",                       │
│    data: {                                                     │
│      user: {...},                                              │
│      google_user: {...},                                       │
│      token: "1|xyz...",                                        │
│      token_type: "Bearer"                                      │
│    }                                                           │
│  }                                                             │
└────────────────────────────────────────────────────────────────┘
```

---

## Middleware Stack

### Request Processing Pipeline

```
┌──────────────────────────────────────────────────────────────┐
│                    Incoming Request                          │
└───────────────────────────┬──────────────────────────────────┘
                            │
                            ▼
                   ┌────────────────┐
                   │  Routing       │
                   │  Middleware    │
                   └────────┬───────┘
                            │
              ┌─────────────┴──────────────┐
              │                            │
      Public Routes               Protected Routes
              │                            │
              ▼                            ▼
     ┌────────────────┐          ┌────────────────┐
     │ /redirect      │          │ auth:sanctum   │
     │ /callback      │          │ middleware     │
     └────────┬───────┘          └────────┬───────┘
              │                            │
              │                            ▼
              │                   ┌────────────────┐
              │                   │ Verify Token   │
              │                   └────────┬───────┘
              │                            │
              │                   ┌────────┴────────┐
              │                   │                 │
              │               Valid Token      Invalid
              │                   │                 │
              │                   │                 ▼
              │                   │          ┌──────────┐
              │                   │          │ 401      │
              │                   │          │Unauth    │
              │                   │          └──────────┘
              │                   │
              └───────────┬───────┘
                          │
                          ▼
                 ┌────────────────┐
                 │  Controller    │
                 │  Action        │
                 └────────┬───────┘
                          │
                          ▼
                 ┌────────────────┐
                 │  Response      │
                 └────────────────┘
```

---

These diagrams provide a comprehensive visual representation of the Google Auth package architecture, flows, and interactions. Use them as a reference for understanding how the system works and for troubleshooting issues.
