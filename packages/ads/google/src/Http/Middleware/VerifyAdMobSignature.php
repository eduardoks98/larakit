<?php

namespace Eduardoks98\AdsGoogle\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Eduardoks98\AdsGoogle\Services\AdMobSsvService;

class VerifyAdMobSignature
{
    public function __construct(
        protected AdMobSsvService $ssvService
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        if (!$this->ssvService->verifyCallback($request)) {
            return response('', 400);
        }

        return $next($request);
    }
}
