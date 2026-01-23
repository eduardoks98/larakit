<?php

namespace Eduardoks98\Recaptcha\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Eduardoks98\Recaptcha\Services\SmartRecaptchaService;
use Symfony\Component\HttpFoundation\Response;

class VerifyRecaptcha
{
    protected SmartRecaptchaService $smartRecaptchaService;

    public function __construct(SmartRecaptchaService $smartRecaptchaService)
    {
        $this->smartRecaptchaService = $smartRecaptchaService;
    }

    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     * @param string $action
     * @param string $contextType Optional context type (login, register, etc.)
     * @return mixed
     */
    public function handle(Request $request, Closure $next, string $action = 'submit', string $contextType = 'unknown')
    {
        if (!config('recaptcha.enabled')) {
            return $next($request);
        }

        // Get token from request
        $token = $request->input('recaptcha_token') ?? $request->header('X-Recaptcha-Token');

        if (empty($token)) {
            return response()->json([
                'type' => 'https://api.example.com/errors/recaptcha-required',
                'title' => 'reCAPTCHA Required',
                'status' => 400,
                'detail' => 'reCAPTCHA token is required',
                'instance' => $request->path(),
            ], 400);
        }

        // Validate with smart context
        $result = $this->smartRecaptchaService->validateWithContext($token, $action, [
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'user_id' => $request->user()?->id,
            'login_context' => $contextType,
        ]);

        if (!$result['success']) {
            return response()->json([
                'type' => 'https://api.example.com/errors/recaptcha-failed',
                'title' => 'reCAPTCHA Validation Failed',
                'status' => 403,
                'detail' => $result['reason'] ?? 'reCAPTCHA validation failed',
                'instance' => $request->path(),
                'score' => $result['score'],
                'trust_score' => $result['trust_score'],
            ], 403);
        }

        // Attach result to request for controller access
        $request->merge(['recaptcha_result' => $result]);

        return $next($request);
    }
}
