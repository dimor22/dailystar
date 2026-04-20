<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureParentAuthenticated
{
    public function handle(Request $request, Closure $next): Response
    {
        $userId = $request->session()->get('parent_user_id');

        if (! $userId) {
            return redirect()->route('parent.login');
        }

        $user = User::find($userId);

        if (! $user || $user->status === 'pending') {
            $request->session()->forget(['parent_user_id', 'parent_timezone']);
            return redirect()->route('parent.pending');
        }

        return $next($request);
    }
}
