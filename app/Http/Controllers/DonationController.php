<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Stripe\Checkout\Session as StripeCheckout;
use Stripe\Stripe;

class DonationController extends Controller
{
    /**
     * Accepted preset amounts in cents.
     * $5 / $10 / $25 / $50 / $100
     */
    private const PRESET_CENTS = [500, 1000, 2500, 5000, 10000];

    /**
     * Redirect to a Stripe Checkout session for a one-time donation.
     */
    public function checkout(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            // amount passed as whole cents (e.g. 2500 = $25.00)
            'amount_cents' => ['required', 'integer', 'min:100', 'max:100000'],
        ]);

        $amountCents = (int) $validated['amount_cents'];

        $secret = (string) config('cashier.secret');

        if (! $secret || str_starts_with($secret, 'sk_test_your') || $secret === '') {
            return redirect()->route('marketing.donate')
                ->with('donation_error', 'Stripe is not configured yet. Please add your STRIPE_SECRET to .env.');
        }

        Stripe::setApiKey($secret);

        $session = StripeCheckout::create([
            'payment_method_types' => ['card'],
            'mode'                 => 'payment',
            'submit_type'          => 'donate',
            'line_items'           => [[
                'price_data' => [
                    'currency'     => 'usd',
                    'unit_amount'  => $amountCents,
                    'product_data' => [
                        'name'        => 'Donation to DailyStars',
                        'description' => 'Support homeschool families with better daily routines.',
                    ],
                ],
                'quantity' => 1,
            ]],
            'success_url' => route('donation.success') . '?session_id={CHECKOUT_SESSION_ID}',
            'cancel_url'  => route('marketing.donate'),
        ]);

        return redirect($session->url);
    }

    /**
     * Thank-you page shown after a successful donation.
     */
    public function success(Request $request): View
    {
        return view('pages.marketing.donate-success');
    }
}
