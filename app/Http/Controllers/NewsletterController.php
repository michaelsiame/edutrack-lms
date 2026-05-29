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

        \App\Models\NewsletterSubscriber::create([
            'email' => $validated['email'],
            'name' => $validated['name'] ?? null,
            'is_active' => true,
            'subscribed_at' => now(),
        ]);

        return back()->with('success', 'You have been subscribed to our newsletter!');
    }

    public function unsubscribe(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        $subscriber = \App\Models\NewsletterSubscriber::where('email', $request->email)->first();

        if ($subscriber) {
            $subscriber->update([
                'is_active' => false,
                'unsubscribed_at' => now(),
            ]);
        }

        return view('newsletter.unsubscribed')->with('success', 'You have been unsubscribed.');
    }
}
