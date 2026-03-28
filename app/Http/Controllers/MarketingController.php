<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;
use Throwable;

class MarketingController extends Controller
{
    public function home(): View
    {
        return view('pages.marketing.home');
    }

    public function about(): View
    {
        return view('pages.marketing.about');
    }

    public function contact(): View
    {
        return view('pages.marketing.contact');
    }

    public function terms(): View
    {
        return view('pages.marketing.terms');
    }

    public function privacy(): View
    {
        return view('pages.marketing.privacy');
    }

    public function donate(): View
    {
        return view('pages.marketing.donate');
    }

    public function submitContact(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:120'],
            'subject' => ['required', 'string', 'max:140'],
            'message' => ['required', 'string', 'min:10', 'max:3000'],
        ]);

        $recipient = (string) config('services.marketing.contact_email');
        $payload = [
            'name' => (string) $validated['name'],
            'email' => (string) $validated['email'],
            'subject' => (string) $validated['subject'],
            'message' => (string) $validated['message'],
        ];

        try {
            Mail::raw(
                "New DailyStars message\n\n"
                . "From: {$payload['name']} <{$payload['email']}>\n"
                . "Subject: {$payload['subject']}\n\n"
                . $payload['message'],
                fn ($mail) => $mail
                    ->to($recipient)
                    ->replyTo($payload['email'], $payload['name'])
                    ->subject('DailyStars Contact: '.$payload['subject'])
            );
        } catch (Throwable $exception) {
            Log::warning('Marketing contact email failed; message logged instead.', [
                'error' => $exception->getMessage(),
                'contact' => $payload,
            ]);

            Log::info('Marketing contact payload', $payload);
        }

        return redirect()
            ->route('marketing.contact')
            ->with('contact_success', 'Thanks for reaching out. I read every message and will reply soon.');
    }
}
