<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SubscriptionController extends Controller
{
    /**
     * Display the Premium / Subscription Hub.
     */
    public function index()
    {
        $user = Auth::user();
        $isPremium = $user->isPremium();
        
        // Mock data for billing history
        $billingHistory = [
            (object)[
                'date' => now()->subMonth()->format('d M Y'),
                'package' => 'Famly Premium Monthly',
                'amount' => 'Rp 19.900',
                'status' => 'Berhasil'
            ],
        ];

        return view('subscription.index', compact('user', 'isPremium', 'billingHistory'));
    }

    /**
     * Handle Subscription Upgrade (Redirect to Checkout)
     */
    public function upgrade(Request $request)
    {
        return redirect()->route('checkout.index', ['type' => 'subscription']);
    }

    /**
     * Manage group-specific one-off payments (Redirect to Checkout)
     */
    public function buyGroupActivation()
    {
        return redirect()->route('checkout.index', ['type' => 'group_activation']);
    }
}
