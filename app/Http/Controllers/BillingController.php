<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BillingController extends Controller
{
    /**
     * Show the billing / subscription management page.
     */
    public function show(Request $request): View
    {
        return view('pages.billing');
    }

    /**
     * Redirect the user to a Stripe Checkout session for the Pro plan.
     */
    public function checkout(Request $request): RedirectResponse
    {
        $user = $this->resolveUser($request);

        if (! $user) {
            return redirect()->route('parent.login');
        }

        $priceId = (string) config('cashier.pro_price_id');

        if (! $priceId || $priceId === 'price_') {
            return redirect()->route('parent.billing')
                ->with('billing_message', 'Stripe is not configured yet. Please set CASHIER_PRO_PRICE_ID in your .env file.');
        }

        $checkout = $user->newSubscription('pro', $priceId)
            ->trialDays(7)
            ->checkout([
                'success_url' => route('parent.billing') . '?checkout=success',
                'cancel_url'  => route('parent.billing')  . '?checkout=cancelled',
            ]);

        return redirect($checkout->url);
    }

    /**
     * Cancel the user's Pro subscription at the end of the billing period.
     */
    public function cancel(Request $request): RedirectResponse
    {
        $user = $this->resolveUser($request);

        if (! $user) {
            return redirect()->route('parent.login');
        }

        $subscription = $user->subscription('pro');

        if ($subscription && $subscription->active()) {
            $subscription->cancel();
        }

        return redirect()->route('parent.billing')
            ->with('billing_message', 'Subscription cancelled. You keep Pro access until the end of your billing period.');
    }

    /**
     * Resume a cancelled subscription that has not yet expired.
     */
    public function resume(Request $request): RedirectResponse
    {
        $user = $this->resolveUser($request);

        if (! $user) {
            return redirect()->route('parent.login');
        }

        $subscription = $user->subscription('pro');

        if ($subscription && $subscription->onGracePeriod()) {
            $subscription->resume();
        }

        return redirect()->route('parent.billing')
            ->with('billing_message', 'Subscription resumed successfully.');
    }

    /**
     * Redirect to the Stripe customer portal for invoice history and payment method changes.
     */
    public function portal(Request $request): RedirectResponse
    {
        $user = $this->resolveUser($request);

        if (! $user) {
            return redirect()->route('parent.login');
        }

        return $user->redirectToBillingPortal(route('parent.billing'));
    }

    // ─── Helpers ─────────────────────────────────────────────────────────────

    private function resolveUser(Request $request): ?User
    {
        $id = $request->session()->get('parent_user_id');

        return $id ? User::find($id) : null;
    }
}
