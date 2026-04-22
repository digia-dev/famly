<?php

namespace App\Http\Controllers;

use App\Models\Tabungan;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

class TransactionController extends Controller
{
    /**
     * Menampilkan daftar transaksi lengkap dengan grouping tanggal.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $filter = $request->get('filter', 'week');

        // Use clean query builder (Scoped by GroupScope)
        $query = Tabungan::with(['kategoriNama', 'kategoriJenis']);

        // Filter Logika
        if ($filter == 'week') {
            $query->where('created_at', '>=', Carbon::now()->startOfWeek());
        } elseif ($filter == 'month') {
            $query->whereMonth('created_at', Carbon::now()->month)
                  ->whereYear('created_at', Carbon::now()->year);
        } elseif ($filter == 'year') {
            $query->whereYear('created_at', Carbon::now()->year);
        }

        $allTransactions = $query->orderBy('created_at', 'desc')->get();

        // Grouping dengan format yang lebih aman
        $groupedTransactions = $allTransactions->groupBy(function($item) {
            return $item->created_at->format('d F Y');
        })->map(function($group) {
            return [
                'items' => $group,
                'total_income' => $group->where('kategoriJenis.jenis', 'Pemasukan')->sum('nominal'),
                'total_expense' => $group->where('kategoriJenis.jenis', 'Pengeluaran')->sum('nominal'),
                'net' => $group->where('kategoriJenis.jenis', 'Pemasukan')->sum('nominal') - $group->where('kategoriJenis.jenis', 'Pengeluaran')->sum('nominal'),
                'date' => $group->first()->created_at
            ];
        });

        return view('transactions.index', compact('groupedTransactions', 'filter'));
    }
    /**
     * Tampilkan form tambah transaksi baru.
     */
    public function create(Request $request)
    {
        $user = Auth::user();
        $groupId = $user->current_group_id;
        
        $selectedWalletId = $request->get('wallet_id');
        $activeWallet = null;
        
        // Get all categories grouped by type (Scoped by GroupScope)
        $wallets = \App\Models\KategoriNamaTabungan::where('wallet_type', 'wallet')->get();
        $posItems = \App\Models\KategoriNamaTabungan::where('wallet_type', 'pos')->get();
        $savings = \App\Models\KategoriNamaTabungan::where('wallet_type', 'savings')->get();
        
        $types = \App\Models\KategoriJenisTabungan::whereIn('jenis', ['Pemasukan', 'Pengeluaran'])->get();

        return view('transactions.create', compact('wallets', 'posItems', 'savings', 'types', 'selectedWalletId', 'activeWallet'));
    }

    /**
     * Simpan transaksi baru dengan logika khusus.
     */
    public function store(Request $request)
    {
        $request->validate([
            'nominal' => 'required|numeric|min:0',
            'keterangan' => 'required|string|max:255',
            'wallet_id' => 'required|exists:kategori_nama_tabungans,id',
            'jenis_id' => 'required|exists:kategori_jenis_tabungans,id',
            'source_wallet_id' => 'nullable|exists:kategori_nama_tabungans,id',
            'linked_pos_id' => 'nullable|exists:kategori_nama_tabungans,id',
            'created_at' => 'nullable|date',
        ]);

        $user = Auth::user();
        $targetWallet = \App\Models\KategoriNamaTabungan::findOrFail($request->wallet_id);
        $jenis = \App\Models\KategoriJenisTabungan::findOrFail($request->jenis_id);

        // RULE: SAVINGS only can be Income (unless role dins)
        if ($targetWallet->wallet_type == 'savings' && $jenis->jenis == 'Pengeluaran' && $user->role != 'dins') {
            return back()->with('error', 'Penarikan Tabungan hanya diperbolehkan oleh Kepala Keluarga.');
        }

        // 1. Record the primary transaction
        $trxData = [
            'nama' => $request->wallet_id,
            'jenis' => $request->jenis_id,
            'nominal' => $request->nominal,
            'keterangan' => $request->keterangan,
            'user_id' => $user->id,
            'created_at' => $request->created_at ?? now(),
        ];

        if ($user->current_group_id) {
            $trxData['group_id'] = $user->current_group_id;
        }

        $transaction = Tabungan::create($trxData);

        // RULE: IF POS EXPENSE -> Deduct from Source Wallet (Dompet)
        if ($targetWallet->wallet_type == 'pos' && $jenis->jenis == 'Pengeluaran' && $request->source_wallet_id) {
            Tabungan::create([
                'nama' => $request->source_wallet_id,
                'jenis' => $jenis->id, // Same expense type
                'nominal' => $request->nominal,
                'keterangan' => "Auto: " . $request->keterangan . " (via Pos " . $targetWallet->nama . ")",
                'user_id' => $user->id,
                'group_id' => $user->current_group_id,
                'status' => 'Lunas',
                'created_at' => $request->created_at ?? now(),
            ]);
        }

        // RULE: IF WALLET EXPENSE + Linked Pos -> Deduct from Linked POS too
        if ($targetWallet->wallet_type == 'wallet' && $jenis->jenis == 'Pengeluaran' && $request->linked_pos_id) {
            Tabungan::create([
                'nama' => $request->linked_pos_id,
                'jenis' => $jenis->id,
                'nominal' => $request->nominal,
                'keterangan' => "Auto: " . $request->keterangan . " (via " . $targetWallet->nama . ")",
                'user_id' => $user->id,
                'group_id' => $user->current_group_id,
                'status' => 'Lunas',
                'created_at' => $request->created_at ?? now(),
            ]);
        }

        return redirect()->route('management.index', ['id' => $request->wallet_id])
            ->with('success', 'Transaksi berhasil dicatat & saldo diperbarui');
    }
}
