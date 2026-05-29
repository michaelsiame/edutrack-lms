<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\NewsletterSubscriber;
use Illuminate\Http\Request;

class NewsletterController extends Controller
{
    public function index()
    {
        $subscribers = NewsletterSubscriber::orderByDesc('subscribed_at')->paginate(50);
        return view('admin.newsletter.index', compact('subscribers'));
    }

    public function toggle(\App\Models\NewsletterSubscriber $subscriber)
    {
        if ($subscriber->is_active) {
            $subscriber->update([
                'is_active' => false,
                'unsubscribed_at' => now(),
            ]);
            return back()->with('success', 'Subscriber deactivated.');
        } else {
            $subscriber->update([
                'is_active' => true,
                'unsubscribed_at' => null,
            ]);
            return back()->with('success', 'Subscriber reactivated.');
        }
    }

    public function destroy(NewsletterSubscriber $subscriber)
    {
        $subscriber->delete();
        return redirect()->route('admin.newsletter.index')->with('success', 'Subscriber removed.');
    }
}
