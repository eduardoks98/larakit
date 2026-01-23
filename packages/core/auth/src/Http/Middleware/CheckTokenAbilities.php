<?php

namespace Eduardoks98\Auth\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Laravel\Sanctum\Exceptions\MissingAbilityException;

class CheckTokenAbilities
{
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     * @param mixed ...$abilities
     * @return mixed
     */
    public function handle(Request $request, Closure $next, ...$abilities)
    {
        if (!$request->user() || !$request->user()->currentAccessToken()) {
            return response()->json([
                'type' => 'https://api.example.com/errors/unauthenticated',
                'title' => 'Unauthenticated',
                'status' => 401,
                'detail' => 'You must be authenticated to access this resource',
                'instance' => $request->path(),
            ], 401);
        }

        $token = $request->user()->currentAccessToken();

        // Check if token has all required abilities
        foreach ($abilities as $ability) {
            if (!$this->hasAbility($token, $ability)) {
                return response()->json([
                    'type' => 'https://api.example.com/errors/forbidden',
                    'title' => 'Forbidden',
                    'status' => 403,
                    'detail' => "This action requires the '{$ability}' permission",
                    'instance' => $request->path(),
                    'required_abilities' => $abilities,
                    'current_abilities' => $token->abilities,
                ], 403);
            }
        }

        return $next($request);
    }

    /**
     * Check if token has a specific ability.
     *
     * @param \Laravel\Sanctum\PersonalAccessToken $token
     * @param string $ability
     * @return bool
     */
    protected function hasAbility($token, string $ability): bool
    {
        // Check for wildcard permission
        if ($token->can('*') || $token->can('admin:*')) {
            return true;
        }

        // Check exact match
        if ($token->can($ability)) {
            return true;
        }

        // Check for wildcard in ability (e.g., "users:*")
        $parts = explode(':', $ability);
        if (count($parts) === 2) {
            $wildcardAbility = $parts[0] . ':*';
            if ($token->can($wildcardAbility)) {
                return true;
            }
        }

        return false;
    }
}
