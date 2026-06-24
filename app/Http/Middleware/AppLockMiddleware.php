<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AppLockMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $allowedRoutes = [
            'unlock.step1',
            'unlock.step1.submit',
            'unlock.step2',
            'unlock.step2.submit',
        ];

        if (in_array($request->route()->getName(), $allowedRoutes)) {
            return $next($request);
        }

        if (!session('unlock_step1')) {
            return redirect()->route('unlock.step1');
        }

        if (!session('unlock_step2')) {
            return redirect()->route('unlock.step2');
        }

        return $next($request);
    }
}