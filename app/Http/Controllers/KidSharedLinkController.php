<?php

namespace App\Http\Controllers;

use App\Models\Kid;
use Illuminate\Http\Request;
use Illuminate\View\View;

class KidSharedLinkController extends Controller
{
    public function __invoke(Request $request, string $publicId): View
    {
        $kid = Kid::query()
            ->with('parent')
            ->where('public_id', $publicId)
            ->firstOrFail();

        $request->session()->forget(['parent_user_id']);
        $request->session()->put('shared_kid_id', $kid->id);
        $request->session()->put('preselected_kid_id', $kid->id);
        $request->session()->put('parent_timezone', (string) ($kid->parent?->timezone ?: config('app.timezone')));

        $activeKidId = (int) $request->session()->get('kid_id', 0);

        if ($activeKidId > 0 && $activeKidId !== (int) $kid->id) {
            $request->session()->forget('kid_id');
        }

        return view('pages.kid-login');
    }
}
