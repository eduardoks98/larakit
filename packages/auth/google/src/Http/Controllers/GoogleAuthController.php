<?php

namespace Eduardoks98\GoogleAuth\Http\Controllers;

use Eduardoks98\BaseApi\Http\Controllers\ApiController;
use Eduardoks98\GoogleAuth\Services\GoogleAuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;

class GoogleAuthController extends ApiController
{
    /**
     * The GoogleAuthService instance.
     */
    protected GoogleAuthService $googleAuthService;

    /**
     * Create a new GoogleAuthController instance.
     */
    public function __construct(GoogleAuthService $googleAuthService)
    {
        $this->googleAuthService = $googleAuthService;
    }

    /**
     * Redirect the user to Google's OAuth page.
     *
     * @OA\Get(
     *     path="/api/auth/google/redirect",
     *     summary="Redirect to Google OAuth",
     *     description="Redirects the user to Google's OAuth 2.0 authorization page",
     *     tags={"Google Authentication"},
     *     @OA\Response(
     *         response=302,
     *         description="Redirect to Google OAuth page"
     *     )
     * )
     */
    public function redirect(Request $request): RedirectResponse|JsonResponse
    {
        try {
            $authorizationUrl = $this->googleAuthService->getAuthorizationUrl();

            // Store state in session for CSRF protection
            $request->session()->put('google_oauth_state', $this->googleAuthService->getState());

            // If the request expects JSON, return the URL instead of redirecting
            if ($request->expectsJson()) {
                return $this->success([
                    'authorization_url' => $authorizationUrl,
                    'state' => $this->googleAuthService->getState(),
                ], 'Authorization URL generated successfully');
            }

            return redirect($authorizationUrl);
        } catch (\Exception $e) {
            return $this->error(
                'Failed to generate authorization URL',
                500,
                ['error' => $e->getMessage()]
            );
        }
    }

    /**
     * Handle the OAuth callback from Google.
     *
     * @OA\Get(
     *     path="/api/auth/google/callback",
     *     summary="Handle Google OAuth callback",
     *     description="Processes the OAuth callback from Google and authenticates the user",
     *     tags={"Google Authentication"},
     *     @OA\Parameter(
     *         name="code",
     *         in="query",
     *         required=true,
     *         description="Authorization code from Google",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="state",
     *         in="query",
     *         required=true,
     *         description="State parameter for CSRF protection",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="User authenticated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Authentication successful"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="user", type="object"),
     *                 @OA\Property(property="google_user", type="object"),
     *                 @OA\Property(property="token", type="string"),
     *                 @OA\Property(property="token_type", type="string", example="Bearer")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid request or state mismatch"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Authentication failed"
     *     )
     * )
     */
    public function callback(Request $request): JsonResponse|RedirectResponse
    {
        try {
            // Check for error from Google
            if ($request->has('error')) {
                $error = $request->get('error');
                $errorDescription = $request->get('error_description', 'Authentication failed');

                if ($request->expectsJson()) {
                    return $this->error($errorDescription, 400, ['error' => $error]);
                }

                return $this->redirectToFrontend(null, $errorDescription);
            }

            // Validate required parameters
            if (! $request->has('code')) {
                if ($request->expectsJson()) {
                    return $this->error('Authorization code not provided', 400);
                }

                return $this->redirectToFrontend(null, 'Authorization code not provided');
            }

            // Verify state to prevent CSRF attacks
            $state = $request->get('state');
            $sessionState = $request->session()->pull('google_oauth_state');

            if (! $state || $state !== $sessionState) {
                if ($request->expectsJson()) {
                    return $this->error('Invalid state parameter', 400);
                }

                return $this->redirectToFrontend(null, 'Invalid state parameter');
            }

            // Handle the callback and authenticate the user
            $result = $this->googleAuthService->handleCallback($request->get('code'));

            // If the request expects JSON, return the result
            if ($request->expectsJson()) {
                return $this->success($result, 'Authentication successful');
            }

            // Redirect to frontend with token
            return $this->redirectToFrontend($result['token']);
        } catch (IdentityProviderException $e) {
            $errorMessage = 'Failed to authenticate with Google: ' . $e->getMessage();

            if ($request->expectsJson()) {
                return $this->error($errorMessage, 401, [
                    'error' => $e->getMessage(),
                    'response_body' => $e->getResponseBody(),
                ]);
            }

            return $this->redirectToFrontend(null, $errorMessage);
        } catch (\Exception $e) {
            $errorMessage = 'Authentication failed: ' . $e->getMessage();

            if ($request->expectsJson()) {
                return $this->error($errorMessage, 500, ['error' => $e->getMessage()]);
            }

            return $this->redirectToFrontend(null, $errorMessage);
        }
    }

    /**
     * Get the authenticated user's Google profile.
     *
     * @OA\Get(
     *     path="/api/auth/google/profile",
     *     summary="Get Google profile",
     *     description="Get the authenticated user's Google profile information",
     *     tags={"Google Authentication"},
     *     security={{"sanctum":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Google profile retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Google profile retrieved successfully"),
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Google account not linked"
     *     )
     * )
     */
    public function profile(Request $request): JsonResponse
    {
        try {
            $user = $request->user();

            $googleUser = $user->googleUser ?? null;

            if (! $googleUser) {
                return $this->error('Google account not linked to this user', 404);
            }

            return $this->success([
                'google_user' => $googleUser->makeVisible(['access_token', 'refresh_token']),
                'is_token_expired' => $googleUser->isTokenExpired(),
            ], 'Google profile retrieved successfully');
        } catch (\Exception $e) {
            return $this->error(
                'Failed to retrieve Google profile',
                500,
                ['error' => $e->getMessage()]
            );
        }
    }

    /**
     * Revoke Google access for the authenticated user.
     *
     * @OA\Delete(
     *     path="/api/auth/google/revoke",
     *     summary="Revoke Google access",
     *     description="Revoke the authenticated user's Google access and unlink the account",
     *     tags={"Google Authentication"},
     *     security={{"sanctum":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Google access revoked successfully"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Google account not linked"
     *     )
     * )
     */
    public function revoke(Request $request): JsonResponse
    {
        try {
            $user = $request->user();

            $googleUser = $user->googleUser ?? null;

            if (! $googleUser) {
                return $this->error('Google account not linked to this user', 404);
            }

            $this->googleAuthService->revokeAccess($googleUser);

            return $this->success(null, 'Google access revoked successfully');
        } catch (\Exception $e) {
            return $this->error(
                'Failed to revoke Google access',
                500,
                ['error' => $e->getMessage()]
            );
        }
    }

    /**
     * Refresh the Google access token.
     *
     * @OA\Post(
     *     path="/api/auth/google/refresh",
     *     summary="Refresh Google access token",
     *     description="Refresh the Google access token using the refresh token",
     *     tags={"Google Authentication"},
     *     security={{"sanctum":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Token refreshed successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Token refreshed successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="access_token", type="string"),
     *                 @OA\Property(property="expires_in", type="integer")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Google account not linked"
     *     )
     * )
     */
    public function refresh(Request $request): JsonResponse
    {
        try {
            $user = $request->user();

            $googleUser = $user->googleUser ?? null;

            if (! $googleUser) {
                return $this->error('Google account not linked to this user', 404);
            }

            $accessToken = $this->googleAuthService->getValidAccessToken($googleUser);

            return $this->success([
                'access_token' => $accessToken,
                'expires_in' => $googleUser->fresh()->expires_in,
            ], 'Token refreshed successfully');
        } catch (\Exception $e) {
            return $this->error(
                'Failed to refresh token',
                500,
                ['error' => $e->getMessage()]
            );
        }
    }

    /**
     * Redirect to the frontend with the authentication token.
     */
    protected function redirectToFrontend(?string $token = null, ?string $error = null): RedirectResponse
    {
        $frontendUrl = config('google-auth.frontend_redirect_url');

        if (! $frontendUrl) {
            $frontendUrl = config('app.url') . '/auth/callback';
        }

        $params = [];

        if ($token) {
            $params['token'] = $token;
        }

        if ($error) {
            $params['error'] = $error;
        }

        $separator = str_contains($frontendUrl, '?') ? '&' : '?';
        $redirectUrl = $frontendUrl . (! empty($params) ? $separator . http_build_query($params) : '');

        return redirect($redirectUrl);
    }
}
