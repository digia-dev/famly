<?php

namespace App\Services;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class PaymentService
{
    protected $midtrans;

    public function __construct(MidtransService $midtrans)
    {
        $this->midtrans = $midtrans;
    }

    /**
     * Create a payment token for Subscription
     */
    public function createSubscriptionToken(User $user)
    {
        $orderId = 'SUB-' . $user->id . '-' . time();
        $grossAmount = 19900; // Rp 19.900

        $customerDetails = [
            'first_name' => $user->name,
            'email' => $user->email,
        ];

        return $this->midtrans->getSnapToken($orderId, $grossAmount, $customerDetails);
    }

    /**
     * Create a payment token for a Single Report Unlock
     */
    public function createReportUnlockToken(User $user, $month, $year)
    {
        $orderId = 'REP-' . $user->id . '-' . $month . $year . '-' . time();
        $grossAmount = 4900; // Rp 4.900

        $customerDetails = [
            'first_name' => $user->name,
            'email' => $user->email,
        ];

        return $this->midtrans->getSnapToken($orderId, $grossAmount, $customerDetails);
    }

    /**
     * Finalize and activate subscription
     */
    public function activateSubscriber(User $user)
    {
        try {
            $user->update([
                'subscription_status' => 'subscriber',
                'subscription_until' => Carbon::now()->addMonth(),
            ]);

            Log::info("User Activated: {$user->email} for premium subscription.");
            return true;
        } catch (\Exception $e) {
            Log::error("Activation Error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Handle Webhook (Callback from Midtrans)
     */
    public function handleWebhook(array $payload)
    {
        $orderId = $payload['order_id'];
        $transactionStatus = $payload['transaction_status'];

        if ($transactionStatus === 'settlement' || $transactionStatus === 'capture') {
            // Split Order ID to identify type
            $parts = explode('-', $orderId);
            $type = $parts[0];
            $userId = $parts[1];

            $user = User::find($userId);
            if (!$user) return false;

            if ($type === 'SUB') {
                return $this->activateSubscriber($user);
            }

            if ($type === 'REP') {
                $monthYear = $parts[2]; // e.g. 052024
                session(['paid_month_' . $monthYear => true]); 
                // Note: In real life, store this in DB instead of session
                return true;
            }
        }

        return false;
    }
}
