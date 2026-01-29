<?php

namespace Eduardoks98\DiscordAuth\Http\Controllers;

use Eduardoks98\BaseApi\Http\Controllers\ApiController;
use Eduardoks98\DiscordAuth\Services\DiscordAuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;

class DiscordAuthController extends ApiController
{
    /**
     * The DiscordAuthService instance.
     */
    protected DiscordAuthService $discordAuthService;

    /**
     * Create a new DiscordAuthController instance.
     */
    public function __construct(DiscordAuthService $discordAuthService)
    {
        $this->discordAuthService = $discordAuthService;
    }

    /**
     * Redirect the user to Discord's OAuth page.
     *
     * @OA\Get(
     *     path="/api/auth/discord/redirect",
     *     summary="Redirect to Discord OAuth",
     *     description="Redirects the user to Discord's OAuth 2.0 authorization page",
     *     tags={"Discord Authentication"},
     *     @OA\Response(
     *         response=302,
     *         description="Redirect to Discord OAuth page"
     *     )
     * )
     */
    public function redirect(Request $request): RedirectResponse|JsonResponse
    {
        try {
            $authorizationUrl = $this->discordAuthService->getAuthorizationUrl();

            // Store state in session for CSRF protection
            $request->session()->put('discord_oauth_state', $this->discordAuthService->getState());

            // If the request expects JSON, return the URL instead of redirecting
            if ($request->expectsJson()) {
                return $this->success([
                    'authorization_url' => $authorizationUrl,
                    'state' => $this->discordAuthService->getState(),
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
     * Handle the OAuth callback from Discord.
     *
     * @OA\Get(
     *     path="/api/auth/discord/callback",
     *     summary="Handle Discord OAuth callback",
     *     description="Processes the OAuth callback from Discord and authenticates the user",
     *     tags={"Discord Authentication"},
     *     @OA\Parameter(
     *         name="code",
     *         in="query",
     *         required=true,
     *         description="Authorization code from Discord",
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
     *                 @OA\Property(property="discord_user", type="object"),
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
            // Check for error from Discord
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
            $sessionState = $request->session()->pull('discord_oauth_state');

            if (! $state || $state !== $sessionState) {
                if ($request->expectsJson()) {
                    return $this->error('Invalid state parameter', 400);
                }

                return $this->redirectToFrontend(null, 'Invalid state parameter');
            }

            // Handle the callback and authenticate the user
            $result = $this->discordAuthService->handleCallback($request->get('code'));

            // If the request expects JSON, return the result
            if ($request->expectsJson()) {
                return $this->success($result, 'Authentication successful');
            }

            // Redirect to frontend with token
            return $this->redirectToFrontend($result['token']);
        } catch (IdentityProviderException $e) {
            $errorMessage = 'Failed to authenticate with Discord: ' . $e->getMessage();

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
     * Get the authenticated user's Discord profile.
     *
     * @OA\Get(
     *     path="/api/auth/discord/profile",
     *     summary="Get Discord profile",
     *     description="Get the authenticated user's Discord profile information",
     *     tags={"Discord Authentication"},
     *     security={{"sanctum":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Discord profile retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Discord profile retrieved successfully"),
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Discord account not linked"
     *     )
     * )
     */
    public function profile(Request $request): JsonResponse
    {
        try {
            $user = $request->user();

            $discordUser = $user->discordUser ?? null;

            if (! $discordUser) {
                return $this->error('Discord account not linked to this user', 404);
            }

            return $this->success([
                'discord_user' => $discordUser->makeVisible(['access_token', 'refresh_token']),
                'is_token_expired' => $discordUser->isTokenExpired(),
            ], 'Discord profile retrieved successfully');
        } catch (\Exception $e) {
            return $this->error(
                'Failed to retrieve Discord profile',
                500,
                ['error' => $e->getMessage()]
            );
        }
    }

    /**
     * Disconnect Discord account from the authenticated user.
     *
     * @OA\Delete(
     *     path="/api/auth/discord/disconnect",
     *     summary="Disconnect Discord account",
     *     description="Disconnect the authenticated user's Discord account",
     *     tags={"Discord Authentication"},
     *     security={{"sanctum":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Discord account disconnected successfully"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Discord account not linked"
     *     )
     * )
     */
    public function disconnect(Request $request): JsonResponse
    {
        try {
            $user = $request->user();

            $discordUser = $user->discordUser ?? null;

            if (! $discordUser) {
                return $this->error('Discord account not linked to this user', 404);
            }

            $this->discordAuthService->revokeAccess($discordUser);

            return $this->success(null, 'Discord account disconnected successfully');
        } catch (\Exception $e) {
            return $this->error(
                'Failed to disconnect Discord account',
                500,
                ['error' => $e->getMessage()]
            );
        }
    }

    /**
     * Refresh the Discord access token.
     *
     * @OA\Post(
     *     path="/api/auth/discord/refresh",
     *     summary="Refresh Discord access token",
     *     description="Refresh the Discord access token using the refresh token",
     *     tags={"Discord Authentication"},
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
     *         description="Discord account not linked"
     *     )
     * )
     */
    public function refresh(Request $request): JsonResponse
    {
        try {
            $user = $request->user();

            $discordUser = $user->discordUser ?? null;

            if (! $discordUser) {
                return $this->error('Discord account not linked to this user', 404);
            }

            $accessToken = $this->discordAuthService->getValidAccessToken($discordUser);

            return $this->success([
                'access_token' => $accessToken,
                'expires_in' => $discordUser->fresh()->expires_in,
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
        $frontendUrl = config('discord-auth.frontend_redirect_url');

        if (! $frontendUrl) {
            $frontendUrl = config('app.url') . '/auth/callback';
        }

        $params = [];

        if ($token) {
            $params['token'] = $token;
            $params['provider'] = 'discord';
        }

        if ($error) {
            $params['error'] = $error;
        }

        $separator = str_contains($frontendUrl, '?') ? '&' : '?';
        $redirectUrl = $frontendUrl . (! empty($params) ? $separator . http_build_query($params) : '');

        return redirect($redirectUrl);
    }
}
