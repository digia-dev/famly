<?php

namespace App\Services;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;

class MidtransService
{
    protected $serverKey;
    protected $clientKey;
    protected $isProduction;
    protected $baseUrl;

    public function __construct()
    {
        $this->serverKey = env('MIDTRANS_SERVER_KEY');
        $this->clientKey = env('MIDTRANS_CLIENT_KEY');
        $this->isProduction = env('MIDTRANS_IS_PRODUCTION', false);
        $this->baseUrl = $this->isProduction 
            ? 'https://app.midtrans.com' 
            : 'https://app.sandbox.midtrans.com';
    }

    /**
     * Get Snap Token for Midtrans Checkout
     */
    public function getSnapToken($orderId, $grossAmount, $customerDetails)
    {
        $client = new Client();
        $auth = base64_encode($this->serverKey . ':');

        try {
            $response = $client->post($this->baseUrl . '/snap/v1/transactions', [
                'headers' => [
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                    'Authorization' => 'Basic ' . $auth,
                ],
                'json' => [
                    'transaction_details' => [
                        'order_id' => $orderId,
                        'gross_amount' => $grossAmount,
                    ],
                    'customer_details' => $customerDetails,
                    'callbacks' => [
                        'finish' => route('checkout.success'),
                    ]
                ],
            ]);

            $result = json_decode($response->getBody()->getContents(), true);
            return $result['token'];
        } catch (\Exception $e) {
            Log::error('Midtrans Snap Error: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Verify Payment Status
     */
    public function getStatus($orderId)
    {
        $client = new Client();
        $auth = base64_encode($this->serverKey . ':');

        try {
            $response = $client->get($this->baseUrl . '/v2/' . $orderId . '/status', [
                'headers' => [
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                    'Authorization' => 'Basic ' . $auth,
                ],
            ]);

            return json_decode($response->getBody()->getContents(), true);
        } catch (\Exception $e) {
            Log::error('Midtrans Status Error: ' . $e->getMessage());
            return null;
        }
    }
}
