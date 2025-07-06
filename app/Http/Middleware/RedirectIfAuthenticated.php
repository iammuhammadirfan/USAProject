<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RedirectIfAuthenticated
{
    public function handle(Request $request, Closure $next, string ...$guards): Response
    {
        foreach ($guards as $guard) {
            if (Auth::guard($guard)->check() && Auth::user()->role == 1) {
                return redirect()->route("admin.dashboard");
            } elseif (Auth::guard($guard)->check() && Auth::user()->role == 2) {
                return redirect()->route("user.dashboard");
            }
        }

        return $next($request);
    }
}

