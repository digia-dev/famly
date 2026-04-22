<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class CheckoutController extends Controller
{
    protected $paymentService;

    public function __construct(\App\Services\PaymentService $paymentService)
    {
        $this->paymentService = $paymentService;
    }

    /**
     * Display the payment method selection page.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $type = $request->get('type', 'subscription'); // 'subscription' or 'report'
        
        $snapToken = null;
        if ($type === 'subscription') {
            $snapToken = $this->paymentService->createSubscriptionToken($user);
        } else if ($type === 'report') {
            $month = $request->get('month', now()->month);
            $year = $request->get('year', now()->year);
            $snapToken = $this->paymentService->createReportUnlockToken($user, $month, $year);
        }
        
        return view('checkout.index', compact('user', 'snapToken', 'type'));
    }

    /**
     * Show the simulated processing screen.
     */
    public function processing(Request $request)
    {
        $method = $request->get('method', 'QRIS / Virtual Account');
        return view('checkout.processing', compact('method'));
    }

    /**
     * Finalize the payment and update user status.
     * Note: This is now largely handled by Webhooks for real payments,
     * but we keep it for manual simulation or fallback.
     */
    public function finalize(Request $request)
    {
        $user = Auth::user();
        
        \DB::transaction(function() use ($user) {
            $this->paymentService->activateSubscriber($user);

            // Record transaction of premium payment
            \App\Models\Tabungan::create([
                'nama' => \App\Models\KategoriNamaTabungan::where('wallet_type', 'wallet')->first()->id ?? 1,
                'jenis' => \App\Models\KategoriJenisTabungan::where('jenis', 'Pengeluaran')->first()->id ?? 2,
                'nominal' => 19900,
                'keterangan' => 'Pembayaran Langganan Premium Famly',
                'user_id' => $user->id,
                'status' => 'verified',
            ]);
        });

        return redirect()->route('checkout.success');
    }

    /**
     * Display the payment success page.
     */
    public function success()
    {
        return view('checkout.success');
    }
}
