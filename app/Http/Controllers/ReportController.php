<?php

namespace App\Http\Controllers;

use App\Models\Tabungan;
use App\Models\KategoriNamaTabungan;
use App\Services\AiFinancialInsight;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    /**
     * Main Premium Dashboard for Reports
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        $groupId = $user->current_group_id;
        $month = $request->get('month', now()->month);
        $year = $request->get('year', now()->year);
        $startDate = Carbon::createFromDate($year, $month, 1)->startOfMonth();
        $endDate = $startDate->copy()->endOfMonth();

        // 1. Unified Monthly Highlights (In vs Out)
        $monthlySums = Tabungan::query()
            ->join('kategori_jenis_tabungans', 'tabungans.jenis', '=', 'kategori_jenis_tabungans.id')
            ->where(function($q) use ($groupId, $user) {
                if ($groupId) $q->where('tabungans.group_id', $groupId);
                else $q->where('tabungans.user_id', $user->id)->whereNull('tabungans.group_id');
            })
            ->whereBetween('tabungans.created_at', [$startDate, $endDate])
            ->selectRaw("
                SUM(CASE WHEN kategori_jenis_tabungans.jenis = 'Pemasukan' THEN tabungans.nominal ELSE 0 END) as total_in,
                SUM(CASE WHEN kategori_jenis_tabungans.jenis = 'Pengeluaran' THEN tabungans.nominal ELSE 0 END) as total_out
            ")
            ->first();

        // 2. High Density Management Breakdown (Pos, Wallets, Savings)
        $categories = KategoriNamaTabungan::all(); // Handled by GroupScope automatically
        
        // Single aggregated query for all category balances in this month
        $catMonthlyStats = Tabungan::query()
            ->join('kategori_jenis_tabungans', 'tabungans.jenis', '=', 'kategori_jenis_tabungans.id')
            ->where(function($q) use ($groupId, $user) {
                if ($groupId) $q->where('tabungans.group_id', $groupId);
                else $q->where('tabungans.user_id', $user->id)->whereNull('tabungans.group_id');
            })
            ->whereBetween('tabungans.created_at', [$startDate, $endDate])
            ->select('tabungans.nama', \DB::raw("
                SUM(CASE WHEN kategori_jenis_tabungans.jenis = 'Pemasukan' THEN tabungans.nominal ELSE 0 END) as income,
                SUM(CASE WHEN kategori_jenis_tabungans.jenis = 'Pengeluaran' THEN tabungans.nominal ELSE 0 END) as expense
            "))
            ->groupBy('tabungans.nama')
            ->get()
            ->keyBy('nama');

        $breakdown = [
            'pos' => ['count' => 0, 'income' => 0, 'expense' => 0],
            'wallet' => ['count' => 0, 'income' => 0, 'expense' => 0],
            'savings' => ['count' => 0, 'income' => 0, 'expense' => 0],
        ];

        foreach ($categories as $cat) {
            $type = $cat->wallet_type ?? 'pos';
            if (isset($breakdown[$type])) {
                $stat = $catMonthlyStats->get($cat->id);
                $breakdown[$type]['count']++;
                $breakdown[$type]['income'] += $stat->income ?? 0;
                $breakdown[$type]['expense'] += $stat->expense ?? 0;
            }
        }

        // 3. Transaction History (NEW)
        $transactions = Tabungan::with(['kategoriJenis', 'kategoriNama'])
            ->whereBetween('created_at', [$startDate, $endDate])
            ->whereHas('kategoriNama') // Ensure valid relationship
            ->whereHas('kategoriJenis') // Ensure valid relationship
            ->orderBy('created_at', 'desc')
            ->take(10)
            ->get();

        // 4. Monetization Context (Business Model)
        $isPremium = $user->isPremium(); 

        // 5. Strict history locking for trial users
        // Rule: Trial only sees current month. 
        // We define "accessible" as the month of today or if they have paid for this specific month.
        $isAccessible = $startDate->isCurrentMonth() || ($startDate->month == now()->month && $startDate->year == now()->year);
        
        if (!$isPremium && !$isAccessible && !session('paid_month_' . $month . '_' . $year)) {
            return view('reports.locked', [
                'startDate' => $startDate,
                'isPremium' => $isPremium,
                'month' => $month,
                'year' => $year
            ]);
        }

        // 6. AI Smart Recommendation / Insight (Real Analysis)
        $aiService = new AiFinancialInsight();
        $aiInsight = $isPremium ? $aiService->getInsight() : "Layanan AI terbatas untuk akun Trial. Upgrade ke Premium untuk mendapatkan wawasan mendalam tentang pengeluaran keluarga Anda.";

        return view('reports.index', compact(
            'monthlySums',
            'breakdown',
            'transactions',
            'aiInsight',
            'startDate',
            'month',
            'year',
            'isPremium'
        ));
    }

    /**
     * Download Financial Report (PDF Logic)
     */
    public function download(Request $request)
    {
        $user = auth()->user();
        $month = $request->get('month', now()->month);
        $year = $request->get('year', now()->year);

        $hasPaid = session('paid_month_' . $month . '_' . $year);

        if (!$user->isPremium() && !$hasPaid) {
            return redirect()->route('reports.index', ['month' => $month, 'year' => $year])
                ->with('error', 'Download laporan terkunci. Silakan upgrade ke Premium atau beli laporan ini.');
        }

        // Simulating PDF generation with a view print
        return view('reports.pdf-template', [
            'user' => $user,
            'month' => $month,
            'year' => $year,
            'is_download' => true
        ]);
    }

    /**
     * Upgrade to Premium (Redirect to Checkout)
     */
    public function upgradeSubscription()
    {
        return redirect()->route('checkout.index', ['type' => 'subscription']);
    }

    /**
     * Simulation: Buy One-Off Report (Redirect to Checkout with Params)
     */
    public function buyOneOffReport(Request $request)
    {
        $month = $request->get('month');
        $year = $request->get('year');
        
        return redirect()->route('checkout.index', [
            'type' => 'report',
            'month' => $month,
            'year' => $year
        ]);
    }

    public function categoryAnalysis(Request $request)
    {
        $user = auth()->user();
        $groupId = $user->current_group_id;
        $month = $request->get('month', now()->month);
        $year = $request->get('year', now()->year);
        $selectedDate = Carbon::createFromDate($year, $month, 1);
        $prevMonthDate = $selectedDate->copy()->subMonth();

        // 1. Consolidated Monthly Summary
        $monthlySums = Tabungan::query()
            ->join('kategori_jenis_tabungans', 'tabungans.jenis', '=', 'kategori_jenis_tabungans.id')
            ->where(function($q) use ($groupId, $user) {
                if ($groupId) $q->where('tabungans.group_id', $groupId);
                else $q->where('tabungans.user_id', $user->id)->whereNull('tabungans.group_id');
            })
            ->where('kategori_jenis_tabungans.jenis', 'Pengeluaran')
            ->selectRaw("
                SUM(CASE WHEN MONTH(tabungans.created_at) = ? AND YEAR(tabungans.created_at) = ? THEN tabungans.nominal ELSE 0 END) as current_spending,
                SUM(CASE WHEN MONTH(tabungans.created_at) = ? AND YEAR(tabungans.created_at) = ? THEN tabungans.nominal ELSE 0 END) as prev_spending
            ", [$month, $year, $prevMonthDate->month, $prevMonthDate->year])
            ->first();

        $totalSpending = $monthlySums->current_spending ?? 0;
        $totalPrevSpending = $monthlySums->prev_spending ?? 0;
        $difference = $totalSpending - $totalPrevSpending;
        $percentageChange = $totalPrevSpending > 0 ? round(($difference / $totalPrevSpending) * 100) : ($totalSpending > 0 ? 100 : 0);

        // 2. Consolidated Weekly Breakdown
        $weeklyData = [1 => 0, 2 => 0, 3 => 0, 4 => 0];
        $weeklyPrevData = [1 => 0, 2 => 0, 3 => 0, 4 => 0];
        
        $rawWeekly = Tabungan::query()
            ->join('kategori_jenis_tabungans', 'tabungans.jenis', '=', 'kategori_jenis_tabungans.id')
            ->where(function($q) use ($groupId, $user) {
                if ($groupId) $q->where('tabungans.group_id', $groupId);
                else $q->where('tabungans.user_id', $user->id)->whereNull('tabungans.group_id');
            })
            ->where('kategori_jenis_tabungans.jenis', 'Pengeluaran')
            ->where(function($q) use ($month, $year, $prevMonthDate) {
                $q->where(function($sub) use ($month, $year) {
                    $sub->whereMonth('tabungans.created_at', $month)->whereYear('tabungans.created_at', $year);
                })->orWhere(function($sub) use ($prevMonthDate) {
                    $sub->whereMonth('tabungans.created_at', $prevMonthDate->month)->whereYear('tabungans.created_at', $prevMonthDate->year);
                });
            })
            ->selectRaw("
                CASE 
                    WHEN DAY(tabungans.created_at) <= 7 THEN 1
                    WHEN DAY(tabungans.created_at) <= 14 THEN 2
                    WHEN DAY(tabungans.created_at) <= 21 THEN 3
                    ELSE 4
                END as week_num,
                SUM(CASE WHEN MONTH(tabungans.created_at) = ? THEN tabungans.nominal ELSE 0 END) as current_nominal,
                SUM(CASE WHEN MONTH(tabungans.created_at) = ? THEN tabungans.nominal ELSE 0 END) as prev_nominal
            ", [$month, $prevMonthDate->month])
            ->groupBy('week_num')
            ->get();

        $maxVal = 0;
        foreach ($rawWeekly as $row) {
            $weeklyData[$row->week_num] = $row->current_nominal;
            $weeklyPrevData[$row->week_num] = $row->prev_nominal;
            $maxVal = max($maxVal, $row->current_nominal, $row->prev_nominal);
        }
        $maxVal = $maxVal > 0 ? $maxVal : 1;

        // 3. Optimized Top Categories with Growth
        $topCategories = Tabungan::query()
            ->join('kategori_nama_tabungans', 'tabungans.nama', '=', 'kategori_nama_tabungans.id')
            ->join('kategori_jenis_tabungans', 'tabungans.jenis', '=', 'kategori_jenis_tabungans.id')
            ->where(function($q) use ($groupId, $user) {
                if ($groupId) $q->where('tabungans.group_id', $groupId);
                else $q->where('tabungans.user_id', $user->id)->whereNull('tabungans.group_id');
            })
            ->where('kategori_jenis_tabungans.jenis', 'Pengeluaran')
            ->whereMonth('tabungans.created_at', $month)
            ->whereYear('tabungans.created_at', $year)
            ->select('tabungans.nama', 'kategori_nama_tabungans.nama as label', DB::raw('SUM(tabungans.nominal) as total'))
            ->groupBy('tabungans.nama', 'label')
            ->orderByDesc('total')
            ->take(5)
            ->get();

        // One-time fetch for previous totals to avoid N+1
        if ($topCategories->isNotEmpty()) {
            $prevTotals = Tabungan::query()
                ->join('kategori_jenis_tabungans', 'tabungans.jenis', '=', 'kategori_jenis_tabungans.id')
                ->where(function($q) use ($groupId, $user) {
                    if ($groupId) $q->where('tabungans.group_id', $groupId);
                    else $q->where('tabungans.user_id', $user->id)->whereNull('tabungans.group_id');
                })
                ->whereIn('tabungans.nama', $topCategories->pluck('nama'))
                ->whereMonth('tabungans.created_at', $prevMonthDate->month)
                ->whereYear('tabungans.created_at', $prevMonthDate->year)
                ->select('tabungans.nama', DB::raw('SUM(tabungans.nominal) as total'))
                ->groupBy('tabungans.nama')
                ->pluck('total', 'nama');

            foreach ($topCategories as $cat) {
                $prevTotal = $prevTotals[$cat->nama] ?? 0;
                $cat->growth = $prevTotal > 0 ? round((($cat->total - $prevTotal) / $prevTotal) * 100, 1) : ($cat->total > 0 ? 100 : 0);
            }
        }

        // AI Insight (Already optimized with internal caching)
        $aiService = new AiFinancialInsight();
        $aiInsight = $aiService->getInsight();

        return view('reports.category-analysis', compact(
            'totalSpending', 
            'percentageChange', 
            'weeklyData', 
            'weeklyPrevData', 
            'maxVal',
            'topCategories', 
            'aiInsight', 
            'selectedDate'
        ));
    }
}
