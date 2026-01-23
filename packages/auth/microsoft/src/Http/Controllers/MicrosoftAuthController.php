<?php

namespace Eduardoks98\MicrosoftAuth\Http\Controllers;

use Eduardoks98\MicrosoftAuth\Services\MicrosoftAuthService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Log;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;

class MicrosoftAuthController
{
    public function __construct(
        protected MicrosoftAuthService $microsoftAuth
    ) {}

    /**
     * Redirect to Microsoft OAuth authorization page.
     */
    public function redirect(Request $request): JsonResponse|RedirectResponse
    {
        try {
            $options = [];

            // Custom scopes
            if ($request->has('scopes')) {
                $options['scopes'] = explode(',', $request->input('scopes'));
            }

            // Custom state
            if ($request->has('state')) {
                $options['state'] = $request->input('state');
            }

            $authUrl = $this->microsoftAuth->getAuthorizationUrl($options);
            $state = $this->microsoftAuth->getState();

            // Store state in session for CSRF validation
            session(['microsoft_oauth_state' => $state]);

            // Return JSON for API or redirect for web
            if ($request->expectsJson()) {
                return response()->json([
                    'authorization_url' => $authUrl,
                    'state' => $state,
                ]);
            }

            return redirect($authUrl);
        } catch (\Exception $e) {
            Log::error('Microsoft OAuth redirect failed', [
                'error' => $e->getMessage(),
            ]);

            if ($request->expectsJson()) {
                return response()->json([
                    'error' => 'Failed to initiate Microsoft authentication',
                    'message' => $e->getMessage(),
                ], 500);
            }

            return redirect(config('microsoft.frontend_redirect_url') . '?error=auth_failed');
        }
    }

    /**
     * Handle OAuth callback from Microsoft.
     */
    public function callback(Request $request): JsonResponse|RedirectResponse
    {
        try {
            // Validate state (CSRF protection)
            $state = $request->input('state');
            $sessionState = session('microsoft_oauth_state');

            if (!$state || !$sessionState || $state !== $sessionState) {
                throw new \Exception('Invalid state parameter');
            }

            // Clear state from session
            session()->forget('microsoft_oauth_state');

            // Check for errors
            if ($request->has('error')) {
                throw new \Exception($request->input('error_description', $request->input('error')));
            }

            // Get authorization code
            $code = $request->input('code');
            if (!$code) {
                throw new \Exception('Authorization code not provided');
            }

            // Exchange code for access token
            $token = $this->microsoftAuth->getAccessToken($code);

            // Get user information
            $userData = $this->microsoftAuth->getUserInfo($token);

            // Create or update Microsoft user
            $microsoftUser = $this->microsoftAuth->findOrCreateMicrosoftUser($userData, $token);

            // Find or create application user
            $user = $this->microsoftAuth->findOrCreateUser($microsoftUser);

            if (!$user) {
                throw new \Exception('Could not create or find user');
            }

            // Create Sanctum token
            $sanctumToken = $user->createToken(
                config('microsoft.token_name'),
                config('microsoft.token_abilities')
            );

            Log::info('User authenticated via Microsoft', [
                'user_id' => $user->id,
                'microsoft_id' => $microsoftUser->microsoft_id,
                'email' => $microsoftUser->email,
            ]);

            // Return JSON for API
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Authentication successful',
                    'token' => $sanctumToken->plainTextToken,
                    'user' => $user,
                    'microsoft_user' => $microsoftUser->makeHidden(['access_token', 'refresh_token']),
                ]);
            }

            // Redirect to frontend with token
            $redirectUrl = config('microsoft.frontend_redirect_url');
            $separator = str_contains($redirectUrl, '?') ? '&' : '?';

            return redirect($redirectUrl . $separator . http_build_query([
                'token' => $sanctumToken->plainTextToken,
                'user_id' => $user->id,
            ]));
        } catch (IdentityProviderException $e) {
            Log::error('Microsoft OAuth callback failed', [
                'error' => $e->getMessage(),
                'response' => $e->getResponseBody(),
            ]);

            if ($request->expectsJson()) {
                return response()->json([
                    'error' => 'Microsoft authentication failed',
                    'message' => $e->getMessage(),
                ], 401);
            }

            return redirect(config('microsoft.frontend_redirect_url') . '?error=auth_failed&message=' . urlencode($e->getMessage()));
        } catch (\Exception $e) {
            Log::error('Microsoft OAuth callback failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            if ($request->expectsJson()) {
                return response()->json([
                    'error' => 'Authentication failed',
                    'message' => $e->getMessage(),
                ], 500);
            }

            return redirect(config('microsoft.frontend_redirect_url') . '?error=auth_failed&message=' . urlencode($e->getMessage()));
        }
    }

    /**
     * Get current authenticated Microsoft user.
     */
    public function me(Request $request): JsonResponse
    {
        try {
            $user = $request->user();

            if (!$user) {
                return response()->json([
                    'error' => 'Unauthenticated',
                ], 401);
            }

            $microsoftUser = $user->microsoftUser;

            if (!$microsoftUser) {
                return response()->json([
                    'error' => 'Microsoft account not linked',
                ], 404);
            }

            return response()->json([
                'user' => $user,
                'microsoft_user' => $microsoftUser->makeHidden(['access_token', 'refresh_token']),
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to get Microsoft user', [
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'error' => 'Failed to get user information',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Refresh Microsoft access token.
     */
    public function refresh(Request $request): JsonResponse
    {
        try {
            $user = $request->user();

            if (!$user) {
                return response()->json([
                    'error' => 'Unauthenticated',
                ], 401);
            }

            $microsoftUser = $user->microsoftUser;

            if (!$microsoftUser || !$microsoftUser->refresh_token) {
                return response()->json([
                    'error' => 'No refresh token available',
                ], 400);
            }

            // Refresh the token
            $token = $this->microsoftAuth->refreshToken($microsoftUser->refresh_token);

            // Update stored tokens
            $microsoftUser->updateTokens(
                $token->getToken(),
                $token->getRefreshToken(),
                $token->getExpires() ? $token->getExpires() - time() : null
            );

            Log::info('Microsoft token refreshed', [
                'user_id' => $user->id,
                'microsoft_id' => $microsoftUser->microsoft_id,
            ]);

            return response()->json([
                'message' => 'Token refreshed successfully',
                'expires_at' => $microsoftUser->token_expires_at,
            ]);
        } catch (IdentityProviderException $e) {
            Log::error('Microsoft token refresh failed', [
                'error' => $e->getMessage(),
                'response' => $e->getResponseBody(),
            ]);

            return response()->json([
                'error' => 'Token refresh failed',
                'message' => $e->getMessage(),
            ], 401);
        } catch (\Exception $e) {
            Log::error('Microsoft token refresh failed', [
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'error' => 'Failed to refresh token',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Unlink Microsoft account.
     */
    public function unlink(Request $request): JsonResponse
    {
        try {
            $user = $request->user();

            if (!$user) {
                return response()->json([
                    'error' => 'Unauthenticated',
                ], 401);
            }

            $microsoftUser = $user->microsoftUser;

            if (!$microsoftUser) {
                return response()->json([
                    'error' => 'Microsoft account not linked',
                ], 404);
            }

            // Clear tokens and unlink
            $microsoftUser->clearTokens();
            $microsoftUser->update(['user_id' => null]);

            Log::info('Microsoft account unlinked', [
                'user_id' => $user->id,
                'microsoft_id' => $microsoftUser->microsoft_id,
            ]);

            return response()->json([
                'message' => 'Microsoft account unlinked successfully',
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to unlink Microsoft account', [
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'error' => 'Failed to unlink Microsoft account',
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
