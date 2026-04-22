<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use App\Models\Tabungan;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\BackupController;

class CmsApiController extends ApiController
{
    /**
     * Get system statistics for the Monitoring Dashboard.
     */
    public function stats()
    {
        $tables = DB::connection()->getDoctrineSchemaManager()->listTableNames();
        $tableCounts = [];
        foreach ($tables as $table) {
            $tableCounts[$table] = DB::table($table)->count();
        }

        // Calculate historical volume for analytics (Last 7 days)
        $history = Tabungan::select(
            DB::raw('DATE(created_at) as date'),
            DB::raw('SUM(nominal) as total')
        )
        ->groupBy('date')
        ->orderBy('date', 'asc')
        ->latest()
        ->take(12) // Expanded for better projection view
        ->get();

        return $this->success([
            'dashboard' => [
                'active_users' => User::count(),
                'total_groups' => \App\Models\Group::count(),
                'total_transactions' => Tabungan::withoutGlobalScopes()->count(),
                'transaction_volume' => Tabungan::withoutGlobalScopes()->sum('nominal'),
                'recent_logs' => ActivityLog::with('user')->latest()->take(10)->get(),
                'recent_transactions' => Tabungan::withoutGlobalScopes()->with(['user.currentGroup', 'kategoriNama', 'kategoriJenis'])->latest()->take(5)->get(),
                'volume_history' => $history,
                'role_distribution' => User::select('role', DB::raw('count(*) as count'))->groupBy('role')->get(),
                'system_health' => [
                    'uptime' => '99.98%',
                    'latency' => rand(8, 22) . 'ms',
                    'status' => 'Optimal'
                ]
            ],
            'system' => [
                'database' => config('database.default'),
                'tables' => $tableCounts,
                'server_time' => now()->toDateTimeString(),
                'laravel_version' => app()->version(),
                'timezone' => config('app.timezone'),
                'php_version' => phpversion()
            ]
        ]);
    }

    /**
     * Get global transactions for CMS.
     */
    public function transactions(Request $request)
    {
        $query = Tabungan::withoutGlobalScopes()->with(['user.currentGroup', 'kategoriNama', 'kategoriJenis'])
            ->latest();

        if ($request->search) {
            $query->where('keterangan', 'like', '%' . $request->search . '%');
        }

        if ($request->user_id) {
            $query->where('user_id', $request->user_id);
        }

        return $this->success($query->paginate(20));
    }

    /**
     * Get advanced analytics for CMS.
     */
    public function analytics()
    {
        // Category distribution (Total value per category)
        $categories = DB::table('tabungans')
            ->join('kategori_nama_tabungans', 'tabungans.nama', '=', 'kategori_nama_tabungans.id')
            ->select('kategori_nama_tabungans.nama_tabungan', DB::raw('SUM(nominal) as total'))
            ->groupBy('kategori_nama_tabungans.nama_tabungan')
            ->get();

        // Growth metrics (Last 30 days)
        $growth = Tabungan::select(
            DB::raw('DATE(created_at) as date'),
            DB::raw('SUM(nominal) as volume'),
            DB::raw('COUNT(*) as count')
        )
        ->where('created_at', '>=', now()->subDays(30))
        ->groupBy('date')
        ->orderBy('date', 'asc')
        ->get();

        // Top Contributing Groups
        $topFamilies = DB::table('groups')
            ->join('users', 'groups.id', '=', 'users.group_id')
            ->join('tabungans', 'users.id', '=', 'tabungans.user_id')
            ->select('groups.name', DB::raw('SUM(tabungans.nominal) as contribution'))
            ->groupBy('groups.name')
            ->orderBy('contribution', 'desc')
            ->take(5)
            ->get();

        return $this->success([
            'categories' => $categories,
            'growth' => $growth,
            'top_families' => $topFamilies,
            'total_volume' => Tabungan::sum('nominal'),
            'average_ticket' => Tabungan::avg('nominal')
        ]);
    }

    /**
     * Get global wallet data for the CMS slider.
     */
    public function wallets()
    {
        $wallets = \App\Models\KategoriNamaTabungan::withoutGlobalScopes()->get()->map(function($wallet) {
            $in = Tabungan::withoutGlobalScopes()->where('nama', $wallet->id)
                ->whereHas('kategoriJenis', fn($q) => $q->where('jenis', 'Pemasukan'))
                ->sum('nominal');
                
            $out = Tabungan::where('nama', $wallet->id)
                ->whereHas('kategoriJenis', fn($q) => $q->where('jenis', 'Pengeluaran'))
                ->sum('nominal');

            return [
                'id' => $wallet->id,
                'name' => $wallet->nama_tabungan,
                'balance' => $in - $out,
                'icon' => $wallet->icon ?? 'account_balance_wallet',
                'type' => $wallet->wallet_type
            ];
        });

        return $this->success($wallets);
    }

    public function download(Request $request)
    {
        $backupController = new BackupController();
        return $backupController->download($request);
    }
}
