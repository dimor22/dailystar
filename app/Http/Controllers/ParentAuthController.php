<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class ParentAuthController extends Controller
{
    public function showLogin(Request $request): View|RedirectResponse
    {
        if ($request->session()->has('parent_user_id')) {
            return redirect()->route('parent.dashboard');
        }

        return view('pages.parent-login');
    }

    public function login(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $parent = User::query()
            ->where('email', $validated['email'])
            ->whereIn('role', ['parent', 'early_adopter', 'admin'])
            ->first();

        if (! $parent || ! Hash::check($validated['password'], $parent->password)) {
            return back()->withErrors([
                'email' => 'Invalid parent credentials.',
            ])->onlyInput('email');
        }

        if ($parent->status === 'pending') {
            return redirect()->route('parent.pending');
        }

        $request->session()->forget(['shared_kid_id', 'preselected_kid_id', 'kid_id']);
        $request->session()->put('parent_user_id', $parent->id);
        $request->session()->put('parent_timezone', $parent->timezone);

        return redirect()->route('parent.dashboard');
    }

    public function logout(Request $request): RedirectResponse
    {
        $request->session()->forget(['parent_user_id', 'parent_timezone', 'shared_kid_id', 'preselected_kid_id', 'kid_id']);

        return redirect()->route('parent.login');
    }
}
