<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ApplyParentTimezone
{
    public function handle(Request $request, Closure $next): Response
    {
        $timezone = (string) $request->session()->get('parent_timezone', '');

        if ($timezone === '') {
            $parentId = (int) $request->session()->get('parent_user_id', 0);

            if ($parentId > 0) {
                $timezone = (string) User::query()
                    ->where('role', 'parent')
                    ->whereKey($parentId)
                    ->value('timezone');

                if ($timezone !== '') {
                    $request->session()->put('parent_timezone', $timezone);
                }
            }
        }

        if ($timezone !== '' && in_array($timezone, timezone_identifiers_list(), true)) {
            config(['app.timezone' => $timezone]);
            date_default_timezone_set($timezone);
        }

        return $next($request);
    }
}
