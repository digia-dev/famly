<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SearchHistory;
use App\Models\KategoriNamaTabungan;
use App\Models\Tabungan;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SearchController extends Controller
{
    /**
     * Get search suggestions (history & popular categories).
     */
    public function suggestions()
    {
        $user = Auth::user();
        
        $history = SearchHistory::where('user_id', $user->id)
            ->latest()
            ->take(5)
            ->get(['query']);

        $popular = KategoriNamaTabungan::select('id', 'nama', 'icon')
            ->addSelect([
                'transaction_count' => Tabungan::selectRaw('count(*)')
                    ->whereColumn('nama', 'kategori_nama_tabungans.id')
            ])
            ->orderBy('transaction_count', 'desc')
            ->take(6)
            ->get();

        return response()->json([
            'history' => $history,
            'popular' => $popular
        ]);
    }

    /**
     * Perform global search and return results.
     */
    public function query(Request $request)
    {
        $q = strtolower($request->query('q'));
        if (!$q || strlen($q) < 2) {
            return response()->json(['results' => []]);
        }

        $user = Auth::user();
        $results = [];

        // ==========================================
        // 1. AI INTENT LOGIC (The Recommendation)
        // ==========================================
        if (str_contains($q, 'sisa') || str_contains($q, 'saldo') || str_contains($q, 'hemat') || str_contains($q, 'berapa')) {
            $aiResult = $this->handleAIFinancialIntent($q);
            if ($aiResult) {
                $results[] = $aiResult;
            }
        }

        // 2. Search Wallets
        $wallets = KategoriNamaTabungan::where('nama', 'like', "%{$q}%")
            ->take(3)
            ->get();
        foreach ($wallets as $w) {
            $results[] = [
                'type' => 'wallet',
                'title' => $w->nama,
                'subtitle' => 'Dompet / Pos Keuangan',
                'icon' => $w->icon ?: 'account_balance_wallet',
                'url' => route('management.index', ['id' => $w->id])
            ];
        }

        // 3. Search Transactions (Scoped by GroupScope)
        $transactions = Tabungan::where('keterangan', 'like', "%{$q}%")
            ->with(['user', 'kategoriNama'])
            ->latest()
            ->take(5)
            ->get();
        foreach ($transactions as $t) {
            $results[] = [
                'type' => 'transaction',
                'title' => $t->keterangan ?: 'Transaksi Tanpa Nama',
                'subtitle' => 'Rp ' . number_format($t->nominal, 0, ',', '.') . ' • ' . ($t->user->name ?? 'User'),
                'icon' => $t->kategoriNama->icon ?? 'receipt_long',
                'url' => route('management.index')
            ];
        }

        // 4. Search Agenda & Rituals
        $agendas = \App\Models\PlannedTransaction::where('keterangan', 'like', "%{$q}%")
            ->orWhere('nama', 'like', "%{$q}%")
            ->take(3)
            ->get();
        foreach ($agendas as $a) {
            $results[] = [
                'type' => 'agenda',
                'title' => $a->keterangan ?: $a->nama,
                'subtitle' => 'Agenda ' . ucfirst($a->activity_type),
                'icon' => $a->activity_type === 'ritual' ? 'celebration' : 'event',
                'url' => route('agenda.index')
            ];
        }

        // 5. Search Group Members
        if ($user->current_group_id) {
            $members = User::whereHas('groups', fn($query) => $query->where('group_id', $user->current_group_id))
                ->where('name', 'like', "%{$q}%")
                ->take(3)
                ->get();
            foreach ($members as $u) {
                $results[] = [
                    'type' => 'user',
                    'title' => $u->name,
                    'subtitle' => 'Anggota Keluarga',
                    'icon' => 'group',
                    'url' => '#'
                ];
            }
        }

        return response()->json(['results' => $results]);
    }

    /**
     * Handle Natural Language Intents for AI Financial Discovery.
     */
    private function handleAIFinancialIntent($query)
    {
        // INTENT 1: Sisa Saldo Bulanan
        if (str_contains($query, 'sisa') && (str_contains($query, 'saldo') || str_contains($query, 'bulan'))) {
            $currentMonthSpending = Tabungan::whereMonth('created_at', now()->month)
                ->whereHas('kategoriJenis', fn($q) => $q->where('jenis', 'Pengeluaran'))
                ->sum('nominal');
                
            // Example Target (In real world, fetch from budget settings)
            $monthlyBudget = 10000000; 
            $remainder = $monthlyBudget - $currentMonthSpending;

            return [
                'type' => 'ai_card',
                'title' => 'Sisa Anggaran Bulanan',
                'subtitle' => 'Berdasarkan rata-rata pengeluaran keluargamu',
                'icon' => 'account_balance_wallet',
                'ai_value' => 'Rp ' . number_format($remainder, 0, ',', '.'),
                'ai_detail' => 'Keluargamu sudah pakai ' . round(($currentMonthSpending/$monthlyBudget)*100) . '% dari budget.',
                'url' => route('reports.analysis')
            ];
        }

        // INTENT 2: Siapa Paling Hemat
        if (str_contains($query, 'siapa') && (str_contains($query, 'hemat') || str_contains($query, 'rajin'))) {
            $activeUser = User::withCount('tabungan')
                ->orderBy('tabungan_count', 'desc')
                ->first();

            if ($activeUser) {
                return [
                    'type' => 'ai_card',
                    'title' => 'Family Hero Minggu Ini',
                    'subtitle' => 'Anggota paling aktif mencatat',
                    'icon' => 'emoji_events',
                    'ai_value' => $activeUser->name,
                    'ai_detail' => "Mencatat {$activeUser->tabungan_count} transaksi. Sangat disiplin!",
                    'url' => route('profile.edit')
                ];
            }
        }

        return null;
    }
}
