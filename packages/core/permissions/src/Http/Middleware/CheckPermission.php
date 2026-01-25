<?php

namespace Eduardoks98\Permissions\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware to check if user has ALL specified permissions.
 *
 * Usage in routes:
 * Route::middleware('permission:admin:users:view')->get('/users', [UserController::class, 'index']);
 * Route::middleware('permission:admin:users:view,admin:users:create')->get('/users', [UserController::class, 'index']);
 */
class CheckPermission
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string ...$permissions): Response
    {
        $user = $request->user();

        if (!$user) {
            return $this->unauthorized($request);
        }

        if (!method_exists($user, 'hasPermissions')) {
            return $this->unauthorized($request);
        }

        if (!$user->hasPermissions($permissions)) {
            return $this->unauthorized($request);
        }

        return $next($request);
    }

    /**
     * Handle unauthorized access.
     */
    protected function unauthorized(Request $request): Response
    {
        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'This action is unauthorized.',
                'error' => 'insufficient_permissions',
            ], 403);
        }

        abort(403, 'This action is unauthorized.');
    }
}
