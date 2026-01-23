<?php

namespace Eduardoks98\FacebookAuth\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Eduardoks98\FacebookAuth\Services\FacebookAuthService;
use Illuminate\Support\Facades\Validator;

/**
 * Facebook Authentication Controller
 *
 * Handles Facebook OAuth2 authentication flow
 */
class FacebookAuthController extends Controller
{
    /**
     * The Facebook authentication service instance.
     *
     * @var FacebookAuthService
     */
    protected FacebookAuthService $facebookAuthService;

    /**
     * Create a new FacebookAuthController instance.
     *
     * @param FacebookAuthService $facebookAuthService
     */
    public function __construct(FacebookAuthService $facebookAuthService)
    {
        $this->facebookAuthService = $facebookAuthService;
    }

    /**
     * Redirect to Facebook for authentication.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function redirect(Request $request): JsonResponse
    {
        try {
            $authUrl = $this->facebookAuthService->getAuthorizationUrl();
            $state = $this->facebookAuthService->getState();

            return response()->json([
                'success' => true,
                'data' => [
                    'authorization_url' => $authUrl,
                    'state' => $state,
                ],
                'message' => 'Facebook authorization URL generated successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to generate Facebook authorization URL',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Handle Facebook OAuth callback.
     *
     * @param Request $request
     * @return JsonResponse|\Illuminate\Http\RedirectResponse
     */
    public function callback(Request $request)
    {
        try {
            // Validate request
            $validator = Validator::make($request->all(), [
                'code' => 'required|string',
                'state' => 'nullable|string',
            ]);

            if ($validator->fails()) {
                return $this->errorResponse(
                    'Invalid callback parameters',
                    $validator->errors()->toArray(),
                    400
                );
            }

            $code = $request->input('code');
            $state = $request->input('state');

            // Handle Facebook callback
            $result = $this->facebookAuthService->handleCallback($code, $state);

            // Check if we should redirect to frontend
            $frontendRedirectUrl = config('facebook-auth.frontend_redirect_url');

            if ($frontendRedirectUrl) {
                // Redirect to frontend with token
                $redirectUrl = $frontendRedirectUrl . '?token=' . urlencode($result['token']);
                return redirect($redirectUrl);
            }

            // Return JSON response
            return response()->json([
                'success' => true,
                'data' => [
                    'access_token' => $result['token'],
                    'token_type' => 'Bearer',
                    'user' => [
                        'id' => $result['user']->id,
                        'name' => $result['user']->name,
                        'email' => $result['user']->email,
                    ],
                    'facebook_user' => [
                        'facebook_id' => $result['facebook_user']->facebook_id,
                        'name' => $result['facebook_user']->name,
                        'email' => $result['facebook_user']->email,
                        'avatar_url' => $result['facebook_user']->avatar_url,
                    ],
                ],
                'message' => 'Facebook authentication successful',
            ]);
        } catch (\League\OAuth2\Client\Provider\Exception\IdentityProviderException $e) {
            return $this->errorResponse(
                'Facebook authentication failed',
                [
                    'error' => $e->getMessage(),
                    'response' => $e->getResponseBody(),
                ],
                401
            );
        } catch (\Exception $e) {
            return $this->errorResponse(
                'An error occurred during Facebook authentication',
                ['error' => $e->getMessage()],
                500
            );
        }
    }

    /**
     * Get the authenticated user's Facebook profile.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function profile(Request $request): JsonResponse
    {
        try {
            $user = $request->user();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthenticated',
                ], 401);
            }

            // Get Facebook user record
            $facebookUser = $user->facebookUser()->first();

            if (!$facebookUser) {
                return response()->json([
                    'success' => false,
                    'message' => 'Facebook profile not found',
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'facebook_id' => $facebookUser->facebook_id,
                    'name' => $facebookUser->name,
                    'email' => $facebookUser->email,
                    'first_name' => $facebookUser->first_name,
                    'last_name' => $facebookUser->last_name,
                    'avatar_url' => $facebookUser->avatar_url,
                    'created_at' => $facebookUser->created_at,
                    'updated_at' => $facebookUser->updated_at,
                ],
                'message' => 'Facebook profile retrieved successfully',
            ]);
        } catch (\Exception $e) {
            return $this->errorResponse(
                'Failed to retrieve Facebook profile',
                ['error' => $e->getMessage()],
                500
            );
        }
    }

    /**
     * Disconnect Facebook account from user.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function disconnect(Request $request): JsonResponse
    {
        try {
            $user = $request->user();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthenticated',
                ], 401);
            }

            // Get Facebook user record
            $facebookUser = $user->facebookUser()->first();

            if (!$facebookUser) {
                return response()->json([
                    'success' => false,
                    'message' => 'Facebook profile not found',
                ], 404);
            }

            // Delete Facebook user record
            $facebookUser->delete();

            return response()->json([
                'success' => true,
                'message' => 'Facebook account disconnected successfully',
            ]);
        } catch (\Exception $e) {
            return $this->errorResponse(
                'Failed to disconnect Facebook account',
                ['error' => $e->getMessage()],
                500
            );
        }
    }

    /**
     * Return error response.
     *
     * @param string $message
     * @param array $errors
     * @param int $statusCode
     * @return JsonResponse
     */
    protected function errorResponse(string $message, array $errors = [], int $statusCode = 400): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $message,
            'errors' => $errors,
        ], $statusCode);
    }
}
