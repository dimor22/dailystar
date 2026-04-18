<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Restrict a route to Pro-plan subscribers only.
 * Redirects to the billing page when the user is on the free plan.
 */
class EnsureProPlan
{
    public function handle(Request $request, Closure $next): Response
    {
        $userId = $request->session()->get('parent_user_id');

        if (! $userId) {
            return redirect()->route('parent.login');
        }

        $user = User::find($userId);

        if (! $user || ! $user->isPro()) {
            return redirect()->route('parent.billing')
                ->with('upgrade_prompt', 'That feature requires the Pro plan.');
        }

        return $next($request);
    }
}
