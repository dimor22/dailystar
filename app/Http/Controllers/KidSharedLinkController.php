<?php

namespace App\Http\Controllers;

use App\Models\Kid;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class KidSharedLinkController extends Controller
{
    public function __invoke(Request $request, string $shareCode): RedirectResponse
    {
        $kid = Kid::query()
            ->where('share_code', strtoupper($shareCode))
            ->firstOrFail();

        $request->session()->put('parent_user_id', $kid->parent_id);
        $request->session()->put('preselected_kid_id', $kid->id);
        $request->session()->forget('kid_id');

        return redirect()->route('kid.login');
    }
}
