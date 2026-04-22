<?php
namespace App\Http\Controllers;

use App\Models\Tabungan;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use App\Models\KategoriNamaTabungan;
use App\Models\KategoriJenisTabungan;
use App\Models\PlannedTransaction;
use App\Helpers\DashboardGreetingHelper;
use App\Services\AiFinancialInsight;

class AdminController extends Controller
{
    /**
     * Menampilkan dashboard utama untuk admin.
     */
    public function index()
    {
        if (Auth::user()->role !== 'dins') {
            abort(403, 'Akses Ditolak');
        }
        
        $user = Auth::user();
        $now = now();
        $groupId = $user->current_group_id;
        $userGroups = $user->groups()->wherePivot('status', 'Active')->get();
        
        $sumsQuery = Tabungan::query()
            ->join('kategori_jenis_tabungans', 'tabungans.jenis', '=', 'kategori_jenis_tabungans.id');
            
        $sums = $sumsQuery->selectRaw("
                SUM(CASE WHEN kategori_jenis_tabungans.jenis = 'Pemasukan' THEN tabungans.nominal ELSE 0 END) as total_in,
                SUM(CASE WHEN kategori_jenis_tabungans.jenis = 'Pengeluaran' THEN tabungans.nominal ELSE 0 END) as total_out,
                SUM(CASE WHEN kategori_jenis_tabungans.jenis = 'Pemasukan' AND MONTH(tabungans.created_at) = ? AND YEAR(tabungans.created_at) = ? THEN tabungans.nominal ELSE 0 END) as month_in,
                SUM(CASE WHEN kategori_jenis_tabungans.jenis = 'Pengeluaran' AND MONTH(tabungans.created_at) = ? AND YEAR(tabungans.created_at) = ? THEN tabungans.nominal ELSE 0 END) as month_out,
                SUM(CASE WHEN kategori_jenis_tabungans.jenis = 'Pengeluaran' AND DATE(tabungans.created_at) = ? THEN tabungans.nominal ELSE 0 END) as today_out,
                SUM(CASE WHEN kategori_jenis_tabungans.jenis = 'Pengeluaran' AND tabungans.created_at >= ? THEN tabungans.nominal ELSE 0 END) as last_30_days_out,
                SUM(CASE WHEN kategori_jenis_tabungans.jenis = 'Pengeluaran' AND MONTH(tabungans.created_at) = ? AND YEAR(tabungans.created_at) = ? THEN tabungans.nominal ELSE 0 END) as last_month_out,
                SUM(CASE WHEN kategori_jenis_tabungans.jenis = 'Pengeluaran' AND tabungans.created_at >= ? AND tabungans.created_at < ? THEN tabungans.nominal ELSE 0 END) as last_2_months_out
            ", [
                $now->month, $now->year,
                $now->month, $now->year,
                $now->toDateString(),
                $now->copy()->subDays(30)->toDateTimeString(),
                $now->copy()->subMonth()->month, $now->copy()->subMonth()->year,
                $now->copy()->subMonths(2)->startOfMonth()->toDateTimeString(),
                $now->copy()->startOfMonth()->toDateTimeString()
            ])
            ->first();

        $totalPemasukan = $sums->total_in ?? 0;
        $totalPengeluaran = $sums->total_out ?? 0;
        $saldoSaatIni = $totalPemasukan - $totalPengeluaran;
        $pemasukanBulanIni = $sums->month_in ?? 0;
        $pengeluaranBulanIni = $sums->month_out ?? 0;
        $pengeluaranHariIni = $sums->today_out ?? 0;
        $rataRataPengeluaranHarian = ($sums->last_30_days_out ?? 0) / 30;
        $expensesLastMonth = $sums->last_month_out ?? 0;
        $expensesLast2Months = $sums->last_2_months_out ?? 0;

        $transaksiTerakhir = Tabungan::with(['kategoriNama', 'kategoriJenis'])->latest()->take(5)->get();
        
        // 2. Optimized Wallet Balance Logic (Optimized with JOIN)
        $allBalancesRaw = Tabungan::query()
            ->join('kategori_jenis_tabungans', 'tabungans.jenis', '=', 'kategori_jenis_tabungans.id')
            ->select('tabungans.nama', \DB::raw("
                SUM(CASE WHEN kategori_jenis_tabungans.jenis = 'Pemasukan' THEN tabungans.nominal ELSE 0 END) 
                - SUM(CASE WHEN kategori_jenis_tabungans.jenis = 'Pengeluaran' THEN tabungans.nominal ELSE 0 END) as final_balance
            "))
            ->groupBy('tabungans.nama')
            ->pluck('final_balance', 'nama'); 

        $userWallets = KategoriNamaTabungan::all();
        
        $walletsWithBalance = $userWallets->map(function($cat) use ($allBalancesRaw) {
            return (object) [
                'type' => $cat->wallet_type,
                'balance' => $allBalancesRaw[$cat->id] ?? 0
            ];
        });

        $totalSavings = $walletsWithBalance->where('type', 'savings')->sum('balance');
        $totalLiquid = $walletsWithBalance->whereIn('type', ['pos', 'wallet'])->sum('balance');
        $totalPos = $walletsWithBalance->where('type', 'pos')->sum('balance');
        $totalWallet = $walletsWithBalance->where('type', 'wallet')->sum('balance');

        // 3. Detailed Savings Progress List (NEW)
        $savingsCategories = KategoriNamaTabungan::where('wallet_type', 'savings')
            ->get();
            
        $savingsList = $savingsCategories->map(function($cat) use ($allBalancesRaw) {
            $balance = $allBalancesRaw[$cat->id] ?? 0;
            $target = $cat->target_saldo ?? 0;
            $percent = $target > 0 ? min(($balance / $target) * 100, 100) : 0;
            $shortfall = $target > 0 ? max($target - $balance, 0) : 0;
            
            return (object) [
                'id' => $cat->id,
                'nama' => $cat->nama,
                'icon' => $cat->icon ?? 'savings',
                'balance' => $balance,
                'target' => $target,
                'percent' => round($percent, 1),
                'shortfall' => $shortfall,
                'color' => $cat->color ?? '#00AA13',
            ];
        });

        // Growth/Progress Metrics
        $totalTargetSavings = $userWallets->where('wallet_type', 'savings')->sum('target_saldo');
        $totalTargetPos = $userWallets->where('wallet_type', 'pos')->sum('target_saldo');
        
        $savingsProgress = $totalTargetSavings > 0 ? ($totalSavings / $totalTargetSavings) * 100 : 0;
        $posUsage = $totalTargetPos > 0 ? (($totalTargetPos - $totalPos) / $totalTargetPos) * 100 : 0;

        // Chart data
        $weeklyChartData = $this->getWeeklyChartData();
        $monthlyChartData = $this->getMonthlyChartData();
        
        // Greeting Helper Logic
        $overallGrowth = $expensesLastMonth > 0 ? (($saldoSaatIni - ($expensesLastMonth)) / $expensesLastMonth) * 0.1 : 5.2; 

        // AI Enhanced Greeting Logic
        $aiService = new AiFinancialInsight();
        $smartTip = cache()->remember('dins_smart_tip_' . Auth::id(), 60 * 6, function () use ($aiService) {
            $pick = $aiService->getSmartPick();
            return $pick['title'] . ": " . $pick['description'];
        });

        $greeting = DashboardGreetingHelper::generateDailyGreeting(
            $pengeluaranHariIni,
            $rataRataPengeluaranHarian,
            $saldoSaatIni,
            [
                'total_savings' => $totalSavings,
                'total_liquid' => $totalLiquid,
                'expenses_last_month' => $expensesLastMonth,
                'expenses_last_2_months' => $expensesLast2Months
            ]
        );
        
        // Override pesan dengan AI
        $greeting['pesan'] = $smartTip;
        
        $aiInsight = $smartTip;
        
        // Fetch real agenda data (Scoped by GroupScope)
        $agendas = PlannedTransaction::with(['kategoriJenis', 'kategoriNama', 'user'])
            ->where(function($q) {
                // Tampilkan yang belum selesai (termasuk yang lewat jatuh tempo)
                // ATAU yang baru saja diselesaikan dalam 12 jam terakhir
                $q->where(function($sub) {
                    $sub->where('status', '!=', 'done')
                        ->whereDate('jatuh_tempo', '<=', now()->toDateString());
                })->orWhere('updated_at', '>=', now()->subHours(12)); 
            })
            ->orderByRaw("CASE WHEN status = 'pending' THEN 0 ELSE 1 END ASC")
            ->orderBy('jatuh_tempo', 'asc')
            ->take(5)
            ->get();
            
        $namaKategori = $userWallets;
        $jenisKategori = KategoriJenisTabungan::all();
        
        return view('admin.dashboard', compact(
            'saldoSaatIni',
            'pemasukanBulanIni',
            'pengeluaranBulanIni',
            'totalPos',
            'totalWallet',
            'totalSavings',
            'savingsProgress',
            'posUsage',
            'overallGrowth',
            'transaksiTerakhir',
            'monthlyChartData',
            'weeklyChartData',
            'greeting',
            'aiInsight',
            'pengeluaranHariIni',
            'namaKategori',
            'jenisKategori',
            'agendas',
            'savingsList',
            'userGroups'
        ));
    }
    
    /**
     * Get chart data untuk 7 hari terakhir
     */
    private function getWeeklyChartData()
    {
        $pengeluaran7Hari = Tabungan::whereHas('kategoriJenis', fn($q) => $q->where('jenis', 'Pengeluaran'))
            ->where('created_at', '>=', now()->subDays(6)->startOfDay())
            ->get()
            ->groupBy(function($date) {
                return Carbon::parse($date->created_at)->format('j');
            })
            ->map(fn($group) => $group->sum('nominal'));
        
        $weeklyLabels = [];
        $weeklyData = [];
        
        for ($i = 6; $i >= 0; $i--) {
            $tanggal = now()->subDays($i);
            $day = $tanggal->format('j');
            
            $weeklyLabels[] = $tanggal->format('j') . ' ' . $tanggal->locale('id')->format('M');
            $weeklyData[] = $pengeluaran7Hari->get($day, 0);
        }
        
        return [
            'labels' => $weeklyLabels,
            'data' => $weeklyData,
            'periode' => '7 Hari Terakhir',
            'total' => array_sum($weeklyData)
        ];
    }
    
    /**
     * Get chart data untuk 1 bulan penuh
     */
    private function getMonthlyChartData()
    {
        $bulanIni = now()->month;
        $tahunIni = now()->year;
        $jumlahHariBulanIni = now()->daysInMonth;
        
        $pengeluaranBulanan = Tabungan::whereHas('kategoriJenis', fn($q) => $q->where('jenis', 'Pengeluaran'))
            ->whereMonth('created_at', $bulanIni)
            ->whereYear('created_at', $tahunIni)
            ->get()
            ->groupBy(function($date) {
                return Carbon::parse($date->created_at)->format('j');
            })
            ->map(fn($group) => $group->sum('nominal'));
        
        $monthlyLabels = [];
        $monthlyData = [];
        
        for ($i = 1; $i <= $jumlahHariBulanIni; $i++) {
            $tanggal = Carbon::create($tahunIni, $bulanIni, $i);
            $monthlyLabels[] = $tanggal->format('j');
            $monthlyData[] = $pengeluaranBulanan->get($i, 0);
        }
        
        return [
            'labels' => $monthlyLabels,
            'data' => $monthlyData,
            'bulan' => now()->locale('id')->isoFormat('MMMM Y'),
            'totalHari' => $jumlahHariBulanIni,
            'total' => array_sum($monthlyData)
        ];
    }

    public function checkIn()
    {
        $user = Auth::user();
        $today = now()->startOfDay();
        $yesterday = now()->subDay()->startOfDay();
        $lastCheckIn = $user->last_check_in_at ? Carbon::parse($user->last_check_in_at)->startOfDay() : null;

        if ($lastCheckIn && $lastCheckIn->equalTo($today)) {
            return response()->json(['success' => false, 'message' => 'Sudah absen hari ini!']);
        }

        if ($lastCheckIn && $lastCheckIn->equalTo($yesterday)) {
            $user->streak_count += 1;
        } else {
            $user->streak_count = 1;
        }

        if ($user->streak_count > $user->highest_streak) {
            $user->highest_streak = $user->streak_count;
        }

        $user->last_check_in_at = now();
        $user->save();

        // Orchestration: Notify group members of the streak
        if ($user->current_group_id) {
            $group = \App\Models\Group::find($user->current_group_id);
            if ($group) {
                $otherMembers = $group->members()->where('user_id', '!=', $user->id)->get();
                foreach ($otherMembers as $member) {
                    \App\Models\Notification::create([
                        'user_id' => $member->id,
                        'title' => 'Streak Bertambah!',
                        'message' => "{$user->name} baru saja absen! Streak saat ini: {$user->streak_count} hari.",
                        'type' => 'success',
                    ]);
                }
            }
        }

        // Check for milestones (3, 7, 14, 30)
        $milestone = null;
        if (in_array($user->streak_count, [3, 7, 14, 30])) {
            $milestone = $user->streak_count;
        }

        return response()->json([
            'success' => true, 
            'message' => 'Absen berhasil!',
            'streak' => $user->streak_count,
            'milestone' => $milestone
        ]);
    }

    public function toggleAgenda($id)
    {
        $agenda = PlannedTransaction::findOrFail($id);
        $agenda->status = $agenda->status === 'done' ? 'pending' : 'done';
        $agenda->save();

        return response()->json([
            'success' => true,
            'status' => $agenda->status,
            'message' => $agenda->status === 'done' ? 'Agenda selesai!' : 'Agenda dikembalikan.'
        ]);
    }
}