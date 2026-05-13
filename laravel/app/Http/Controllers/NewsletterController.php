<?php

namespace App\Http\Controllers;

use App\Models\NewsletterSubscriber;
use Illuminate\Http\Request;

class NewsletterController extends Controller
{
    public function subscribe(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|email|max:255|unique:newsletter_subscribers,email',
            'name' => 'nullable|string|max:100',
        ]);

        NewsletterSubscriber::create([
            'email' => $validated['email'],
            'name' => $validated['name'] ?? null,
            'is_active' => true,
            'subscribed_at' => now(),
        ]);

        return back()->with('success', 'Thank you for subscribing to our newsletter!');
    }

    public function unsubscribe(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|email',
        ]);

        $subscriber = NewsletterSubscriber::where('email', $validated['email'])->first();

        if ($subscriber) {
            $subscriber->update(['is_active' => false]);
            return redirect()->route('home')->with('success', 'You have been unsubscribed from our newsletter.');
        }

        return redirect()->route('home')->with('info', 'Email not found in our subscriber list.');
    }
}
