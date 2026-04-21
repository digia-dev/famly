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
    public function categoryAnalysis(Request $request)
    {
        $user = auth()->user();
        $familyId = $user->family_id;
        $month = $request->get('month', now()->month);
        $year = $request->get('year', now()->year);
        $selectedDate = Carbon::createFromDate($year, $month, 1);
        $prevMonthDate = $selectedDate->copy()->subMonth();

        // 1. Consolidated Monthly Summary
        $monthlySums = Tabungan::query()
            ->join('kategori_jenis_tabungans', 'tabungans.jenis', '=', 'kategori_jenis_tabungans.id')
            ->where('tabungans.family_id', $familyId)
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
            ->where('tabungans.family_id', $familyId)
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
            ->where('tabungans.family_id', $familyId)
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
                ->where('tabungans.family_id', $familyId)
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
