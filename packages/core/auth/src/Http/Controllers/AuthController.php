<?php

namespace Eduardoks98\Auth\Http\Controllers;

use Eduardoks98\BaseApi\Http\Controllers\ApiController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Eduardoks98\Auth\Services\TokenService;
use Eduardoks98\Auth\Services\SessionService;

class AuthController extends ApiController
{
    protected TokenService $tokenService;
    protected SessionService $sessionService;

    public function __construct(TokenService $tokenService, SessionService $sessionService)
    {
        $this->tokenService = $tokenService;
        $this->sessionService = $sessionService;
    }

    /**
     * Login user and return access token.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(Request $request)
    {
        $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
            'device_name' => 'nullable|string|max:255',
        ]);

        $user = $this->findUser($request->input('username'));

        if (!$user || !Hash::check($request->input('password'), $user->password)) {
            throw ValidationException::withMessages([
                'username' => ['The provided credentials are incorrect.'],
            ]);
        }

        // Check if account is active
        if (isset($user->active) && !$user->active) {
            return problemDetails(
                'https://api.example.com/errors/account-inactive',
                'Account Inactive',
                403,
                'Your account has been deactivated. Please contact support.',
                $request->path()
            );
        }

        // Generate device ID
        $deviceId = $this->tokenService->generateDeviceId($request);
        $deviceName = $request->input('device_name', $request->userAgent());

        // Create tokens
        $accessToken = $this->tokenService->createAccessToken($user, $deviceName, $deviceId);
        $refreshToken = $this->tokenService->createRefreshToken($user, $deviceName, $deviceId);

        // Track session
        $this->sessionService->createSession($user, $request, $deviceId);

        // Log successful login
        if (config('auth.login_security.log_attempts')) {
            \Log::info("User {$user->id} logged in from {$request->ip()}");
        }

        return $this->success([
            'access_token' => $accessToken->plainTextToken,
            'refresh_token' => $refreshToken->plainTextToken,
            'token_type' => 'Bearer',
            'expires_in' => config('auth.sanctum.access_token_expiration', 15) * 60,  // seconds
            'refresh_expires_in' => config('auth.sanctum.refresh_token_expiration', 10080) * 60,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
            ],
        ]);
    }

    /**
     * Refresh access token using refresh token.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function refresh(Request $request)
    {
        $request->validate([
            'refresh_token' => 'required|string',
        ]);

        $refreshTokenString = $request->input('refresh_token');

        try {
            $result = $this->tokenService->refreshAccessToken($refreshTokenString);

            return $this->success([
                'access_token' => $result['access_token'],
                'refresh_token' => $result['refresh_token'],
                'token_type' => 'Bearer',
                'expires_in' => config('auth.sanctum.access_token_expiration', 15) * 60,
                'refresh_expires_in' => config('auth.sanctum.refresh_token_expiration', 10080) * 60,
            ]);
        } catch (\Exception $e) {
            return problemDetails(
                'https://api.example.com/errors/invalid-refresh-token',
                'Invalid Refresh Token',
                401,
                $e->getMessage(),
                $request->path()
            );
        }
    }

    /**
     * Logout user (revoke current token).
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout(Request $request)
    {
        $user = $request->user();
        $revokeAll = $request->query('all', false);

        if ($revokeAll) {
            // Revoke all tokens
            $user->tokens()->delete();
            $this->sessionService->endAllSessions($user);

            return $this->success([
                'message' => 'All tokens have been revoked',
            ]);
        }

        // Revoke only current token
        $request->user()->currentAccessToken()->delete();

        // End session for current device
        if (config('auth.sanctum.device_id_enabled')) {
            $deviceId = $this->tokenService->generateDeviceId($request);
            $this->sessionService->endSession($user, $deviceId);
        }

        return $this->success([
            'message' => 'Logged out successfully',
        ]);
    }

    /**
     * Get authenticated user information.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function me(Request $request)
    {
        $user = $request->user();

        return $this->success([
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'created_at' => $user->created_at,
        ]);
    }

    /**
     * Get all active sessions for authenticated user.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function sessions(Request $request)
    {
        $user = $request->user();
        $sessions = $this->sessionService->getUserSessions($user);

        return $this->success([
            'sessions' => $sessions,
        ]);
    }

    /**
     * Revoke a specific session/device.
     *
     * @param Request $request
     * @param string $deviceId
     * @return \Illuminate\Http\JsonResponse
     */
    public function revokeSession(Request $request, string $deviceId)
    {
        $user = $request->user();

        $this->tokenService->revokeDeviceTokens($user, $deviceId);
        $this->sessionService->endSession($user, $deviceId);

        return $this->success([
            'message' => 'Session revoked successfully',
        ]);
    }

    /**
     * Find user by username (email or username field).
     *
     * @param string $username
     * @return mixed
     */
    protected function findUser(string $username)
    {
        $userModel = config('auth.providers.users.model', \App\Models\User::class);

        // Try email first
        $user = $userModel::where('email', $username)->first();

        // If not found, try username field if it exists
        if (!$user && \Schema::hasColumn((new $userModel)->getTable(), 'username')) {
            $user = $userModel::where('username', $username)->first();
        }

        return $user;
    }
}
