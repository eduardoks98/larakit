<?php

namespace Eduardoks98\MicrosoftAuth\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Eduardoks98\MicrosoftAuth\Services\MicrosoftAuthService;
use Illuminate\Support\Facades\Log;

class EnsureMicrosoftTokenIsValid
{
    public function __construct(
        protected MicrosoftAuthService $microsoftAuth
    ) {}

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
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
            ], 403);
        }

        // Check if token is expired and refresh if possible
        if ($microsoftUser->isTokenExpired() && $microsoftUser->refresh_token) {
            try {
                $token = $this->microsoftAuth->refreshToken($microsoftUser->refresh_token);

                $microsoftUser->updateTokens(
                    $token->getToken(),
                    $token->getRefreshToken(),
                    $token->getExpires() ? $token->getExpires() - time() : null
                );

                Log::info('Microsoft token auto-refreshed', [
                    'user_id' => $user->id,
                    'microsoft_id' => $microsoftUser->microsoft_id,
                ]);
            } catch (\Exception $e) {
                Log::error('Failed to auto-refresh Microsoft token', [
                    'error' => $e->getMessage(),
                    'user_id' => $user->id,
                ]);

                return response()->json([
                    'error' => 'Microsoft token expired and could not be refreshed',
                    'message' => 'Please re-authenticate with Microsoft',
                ], 401);
            }
        }

        // Attach Microsoft user to request for easy access
        $request->attributes->set('microsoft_user', $microsoftUser);

        return $next($request);
    }
}
