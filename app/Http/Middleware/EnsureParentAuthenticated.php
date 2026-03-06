<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureParentAuthenticated
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->session()->has('parent_user_id')) {
            return redirect()->route('parent.login');
        }

        return $next($request);
    }
}
