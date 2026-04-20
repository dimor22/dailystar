<?php

namespace App\Http\Middleware;

use App\Enums\Role;
use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        $userId = $request->session()->get('parent_user_id');

        if (! $userId) {
            return redirect()->route('parent.login');
        }

        $user = User::find($userId);

        if (! $user || $user->role !== Role::Admin) {
            abort(403, 'Admin access required.');
        }

        return $next($request);
    }
}
