<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Tabungan;
use App\Models\PlannedTransaction;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class NotificationController extends Controller
{
    /**
     * Display the notification page.
     */
    public function index()
    {
        $user = Auth::user();
        $familyId = $user->family_id;

        // 1. Aktivitas Keuangan (Real Data from Tabungan)
        $financialTransactions = Tabungan::with(['user', 'kategoriNama'])
            ->where('family_id', $familyId)
            ->latest()
            ->take(5)
            ->get();

        $financial = $financialTransactions->map(function ($t) {
            return [
                'id' => $t->id,
                'wallet_id' => $t->nama, // Assuming 'nama' stores the KategoriNamaTabungan ID
                'title' => $t->keterangan ?? 'Transaksi Baru',
                'category' => $t->kategoriNama->nama ?? 'UMUM',
                'amount' => 'Rp ' . number_format($t->nominal, 0, ',', '.'),
                'user' => $t->user->name ?? 'Anggota',
                'type' => $t->nominal >= 1000000 ? 'PREMIUM' : 'RECENT',
                'time' => $t->created_at->isToday() ? 'HARI INI' : $t->created_at->diffForHumans(),
                'detail' => 'telah dicatat oleh ' . ($t->user->name ?? 'Anggota')
            ];
        });

        // 2. Pengingat Tagihan (Real Data from PlannedTransaction)
        $planned = PlannedTransaction::where('family_id', $familyId)
            ->where('jatuh_tempo', '>=', now()->startOfDay())
            ->orderBy('jatuh_tempo', 'asc')
            ->take(5)
            ->get();

        $bills = $planned->map(function ($p) {
            $days = now()->startOfDay()->diffInDays($p->jatuh_tempo, false);
            
            $status = 'H-' . $days . ' JATUH TEMPO';
            $status_type = 'error';
            
            if ($days == 0) $status = 'HARI INI';
            if ($days == 1) $status = 'ESOK HARI';
            if ($days > 3) $status_type = 'secondary';
            if ($p->status == 'completed') {
                $status = 'DIBAYAR OTOMATIS';
                $status_type = 'primary';
            }

            return [
                'title' => $p->nama,
                'status' => $status,
                'status_type' => $status_type,
                'icon' => $this->getIconForBill($p->nama),
                'detail' => 'Estimasi tagihan: Rp ' . number_format($p->nominal, 0, ',', '.')
            ];
        });

        // 3. Update Tugas (Dynamic Mock if no Task model yet, but using Family Members)
        $familyMembers = User::where('family_id', $familyId)->where('id', '!=', $user->id)->get();
        $tasks = [];
        if ($familyMembers->count() > 0) {
            foreach ($familyMembers as $member) {
                $tasks[] = [
                    'user' => $member->name,
                    'task' => 'Pembaruan Aktivitas',
                    'time' => Carbon::now()->subMinutes(rand(10, 120))->format('H:i') . ' WIB',
                    'points' => '+' . rand(10, 50),
                    'avatar' => 'https://ui-avatars.com/api/?name=' . urlencode($member->name) . '&background=006d36&color=fff'
                ];
            }
        }

        $notifications = [
            'financial' => $financial,
            'bills' => $bills,
            'tasks' => collect($tasks)->take(3)
        ];

        return view('notification.index', compact('notifications'));
    }

    /**
     * Helper to get icon based on bill name.
     */
    private function getIconForBill($name)
    {
        $name = strtolower($name);
        if (str_contains($name, 'listrik') || str_contains($name, 'pln')) return 'bolt';
        if (str_contains($name, 'wifi') || str_contains($name, 'internet')) return 'wifi';
        if (str_contains($name, 'air') || str_contains($name, 'pdam')) return 'water_drop';
        return 'receipt_long';
    }
}
