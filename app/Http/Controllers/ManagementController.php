<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\KategoriNamaTabungan;
use App\Models\Tabungan;
use Illuminate\Support\Facades\Auth;
use App\Services\AiFinancialInsight;

class ManagementController extends Controller
{
    protected $aiService;

    public function __construct(AiFinancialInsight $aiService)
    {
        $this->aiService = $aiService;
    }

    /**
     * Unified Management Dashboard (Pos, Dompet, Tabungan) in one view.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $familyId = $user->family_id;
        
        // Determine active type from query param, default to 'pos'
        $type = $request->get('type', 'pos');
        $id = $request->get('id');

        // Check if we need to show the detail view
        if ($id) {
            $kategori = KategoriNamaTabungan::where('family_id', $familyId)->findOrFail($id);
            $walletData = $this->calculateBalances(collect([$kategori]))->first();
            $transactions = Tabungan::with('kategoriJenis')
                ->where('nama', $id)
                ->where('family_id', $familyId)
                ->orderBy('created_at', 'desc')
                ->get();

            return view('management.show', compact('kategori', 'walletData', 'transactions', 'type'));
        }
        
        // Map types to database wallet_types
        $typeMap = [
            'pos' => 'pos',
            'dompet' => 'wallet',
            'tabungan' => 'savings'
        ];
        
        $walletType = $typeMap[$type] ?? 'pos';

        // Fetch categories for the specific type
        $categories = KategoriNamaTabungan::where('family_id', $familyId)
            ->where('wallet_type', $walletType)
            ->get();

        $wallets = $this->calculateBalances($categories);
        
        // Calculate dynamic monthly metrics for the Hero Section
        $now = now();
        $startOfMonth = $now->copy()->startOfMonth();
        $startOfLastMonth = $now->copy()->subMonth()->startOfMonth();
        $endOfLastMonth = $now->copy()->subMonth()->endOfMonth();

        // Monthly Transactions for this category type
        $monthlyTransactions = Tabungan::with('kategoriJenis')
            ->where('family_id', $familyId)
            ->whereIn('nama', $categories->pluck('id'))
            ->whereBetween('created_at', [$startOfMonth, $now])
            ->get();

        $lastMonthTransactions = Tabungan::with('kategoriJenis')
            ->where('family_id', $familyId)
            ->whereIn('nama', $categories->pluck('id'))
            ->whereBetween('created_at', [$startOfLastMonth, $endOfLastMonth])
            ->get();

        $monthlyIncome = $monthlyTransactions->where('kategoriJenis.jenis', 'Pemasukan')->sum('nominal');
        $monthlyExpense = $monthlyTransactions->where('kategoriJenis.jenis', 'Pengeluaran')->sum('nominal');

        $lastMonthIncome = $lastMonthTransactions->where('kategoriJenis.jenis', 'Pemasukan')->sum('nominal');
        $lastMonthExpense = $lastMonthTransactions->where('kategoriJenis.jenis', 'Pengeluaran')->sum('nominal');

        // Growth logic
        $incomeGrowth = $lastMonthIncome > 0 ? (($monthlyIncome - $lastMonthIncome) / $lastMonthIncome) * 100 : 0;
        $expenseGrowth = $lastMonthExpense > 0 ? (($monthlyExpense - $lastMonthExpense) / $lastMonthExpense) * 100 : 0;
        
        // Unified balance/allocation logic
        $totalAllocation = 0;
        $totalSpent = 0;
        $totalBalance = 0;
        
        if ($type === 'pos') {
            $totalAllocation = $wallets->sum('target_saldo');
            $totalSpent = $wallets->sum('spent');
            $totalBalance = $totalAllocation - $totalSpent;
        } else {
            $totalBalance = $wallets->sum('balance'); 
        }

        // Fetch AI Insights
        $aiInsight = $this->aiService->getManagementInsights();

        return view('management.index', compact(
            'wallets', 
            'type', 
            'totalAllocation', 
            'totalSpent', 
            'totalBalance', 
            'monthlyIncome',
            'monthlyExpense',
            'incomeGrowth',
            'expenseGrowth',
            'aiInsight'
        ));
    }

    public function create(Request $request)
    {
        $type = $request->get('type', 'pos');
        
        // Fetch real AI tip for the create page
        $aiTip = $this->aiService->getCreatePageTip($type === 'pos' ? 'pos budget' : ($type === 'tabungan' ? 'tabungan/savings' : 'dompet/wallet'));
        
        return view('kategori.nama-create', compact('type', 'aiTip'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama' => 'required|string|max:255',
            'kategori_kas' => 'nullable|string',
            'target_saldo' => 'nullable|numeric|min:0',
            'icon' => 'nullable|string',
            'wallet_type' => 'nullable|string|in:pos,wallet,savings',
            'status' => 'nullable|string',
            'color' => 'nullable|string',
            'description' => 'nullable|string',
        ]);

        // 1. Create the Category/Wallet Entity
        $wallet = KategoriNamaTabungan::create([
            'nama' => $request->nama,
            'kategori_kas' => $request->kategori_kas,
            'target_saldo' => $request->target_saldo,
            'icon' => $request->icon ?? 'account_balance',
            'wallet_type' => $request->wallet_type ?? 'pos',
            'status' => $request->status ?? 'AKTIF',
            'color' => $request->color ?? '#006d36',
            'description' => $request->description,
            'family_id' => auth()->user()->family_id,
        ]);

        // 2. Logic: If there is an initial balance/target (for Pos/Wallet), 
        // create an initial transaction so the logic calculated in index is correct.
        // We look for a 'Initial Balance' category in kategori_jenis_tabungans or create one.
        if ($request->target_saldo > 0 && ($request->wallet_type == 'pos' || $request->wallet_type == 'wallet')) {
            // Find or fallback to a generic income type
            $jenis = \App\Models\KategoriJenisTabungan::where('jenis', 'Pemasukan')->first();
            
            if ($jenis) {
                Tabungan::create([
                    'nama' => $wallet->id,
                    'jenis' => $jenis->id,
                    'nominal' => $request->target_saldo,
                    'keterangan' => 'Saldo Awal: ' . $wallet->nama,
                    'family_id' => auth()->user()->family_id,
                    'created_at' => now(),
                ]);
            }
        }

        $type = $request->wallet_type == 'wallet' ? 'dompet' : ($request->wallet_type == 'savings' ? 'tabungan' : 'pos');
        return redirect()->route('management.index', ['type' => $type])->with('success', 'Manajemen item berhasil ditambahkan');
    }

    public function edit($id)
    {
        $kategori = KategoriNamaTabungan::findOrFail($id);
        return view('kategori.nama.edit', compact('kategori'));
    }

    public function update(Request $request, $id)
    {
        $kategori = KategoriNamaTabungan::findOrFail($id);
        $request->validate([
            'nama' => 'required|string|max:255|unique:kategori_nama_tabungans,nama,' . $id,
        ]);

        $kategori->update($request->all());

        $type = $kategori->wallet_type == 'wallet' ? 'dompet' : ($kategori->wallet_type == 'savings' ? 'tabungan' : 'pos');
        return redirect()->route('management.index', ['type' => $type])->with('success', 'Manajemen item berhasil diupdate');
    }

    public function destroy($id)
    {
        $kategori = KategoriNamaTabungan::findOrFail($id);
        $type = $kategori->wallet_type == 'wallet' ? 'dompet' : ($kategori->wallet_type == 'savings' ? 'tabungan' : 'pos');
        $kategori->delete();

        return redirect()->route('management.index', ['type' => $type])->with('success', 'Manajemen item berhasil dihapus');
    }

    /**
     * Update only the name via AJAX for inline editing.
     */
    public function updateName(Request $request, $id)
    {
        $request->validate([
            'nama' => 'required|string|max:255',
        ]);

        $kategori = KategoriNamaTabungan::where('family_id', Auth::user()->family_id)->findOrFail($id);
        $kategori->update(['nama' => $request->nama]);

        return response()->json(['success' => true, 'nama' => $kategori->nama]);
    }

    /**
     * Helper to calculate balances for a collection of categories.
     */
    private function calculateBalances($categories)
    {
        if ($categories->isEmpty()) return collect();

        $familyId = Auth::user()->family_id;
        $categoryIds = $categories->pluck('id')->toArray();

        // Single aggregated query to replace the N+1 loop
        $stats = Tabungan::query()
            ->join('kategori_jenis_tabungans', 'tabungans.jenis', '=', 'kategori_jenis_tabungans.id')
            ->where('tabungans.family_id', $familyId)
            ->whereIn('tabungans.nama', $categoryIds)
            ->select('tabungans.nama as category_id', \DB::raw("
                SUM(CASE WHEN kategori_jenis_tabungans.jenis = 'Pemasukan' THEN tabungans.nominal ELSE 0 END) as total_in,
                SUM(CASE WHEN kategori_jenis_tabungans.jenis = 'Pengeluaran' THEN tabungans.nominal ELSE 0 END) as total_out
            "))
            ->groupBy('tabungans.nama')
            ->get()
            ->keyBy('category_id');

        return $categories->map(function($cat) use ($stats) {
            $stat = $stats->get($cat->id);
            $totalIn = $stat->total_in ?? 0;
            $totalOut = $stat->total_out ?? 0;
            $balance = $totalIn - $totalOut;
            
            $spent = $totalOut; 
            $remaining = ($cat->target_saldo ?? 0) - $spent;
            $percent = ($cat->target_saldo > 0) ? ($spent / $cat->target_saldo) * 100 : 0;
            $balancePercent = ($cat->target_saldo > 0) ? ($balance / $cat->target_saldo) * 100 : 0;

            return (object) [
                'id' => $cat->id,
                'nama' => $cat->nama,
                'description' => $cat->description,
                'kategori_kas' => $cat->kategori_kas,
                'icon' => $cat->icon ?? 'payments',
                'target_saldo' => $cat->target_saldo,
                'spent' => $spent,
                'remaining' => $remaining,
                'balance' => $balance,
                'percent' => min($percent, 100),
                'balance_percent' => min($balancePercent, 100),
                'color' => $cat->color ?? '#006d36',
                'status' => $cat->status ?? 'AKTIF',
            ];
        });
    }
}
