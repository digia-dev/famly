<?php

namespace App\Http\Controllers;

use App\Models\KategoriJenisTabungan;
use App\Models\KategoriNamaTabungan;
use App\Models\PlannedTransaction;
use App\Models\Tabungan;
use Illuminate\Http\Request;

class PlannedTransactionController extends Controller
{
    public function index(Request $request)
    {
        $transactions = PlannedTransaction::with(['kategoriNama', 'kategoriJenis'])
            ->where('status', 'active')
            ->orderBy('jatuh_tempo', 'asc')
            ->get();

        // Categorize for UI
        $bills = $transactions->filter(fn($t) => in_array($t->kategoriJenis->nama, ['Pengeluaran', 'Tagihan']));
        $tasks = $transactions->filter(fn($t) => !in_array($t->kategoriJenis->nama, ['Pengeluaran', 'Tagihan']));
        
        $streakCount = 7;
        
        return view('planned-transactions.index', compact('transactions', 'bills', 'tasks', 'streakCount'));
    }

    public function create()
    {
        $namaKategori = KategoriNamaTabungan::all();
        $jenisKategori = KategoriJenisTabungan::all();
        return view('planned-transactions.create', compact('namaKategori', 'jenisKategori'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama' => 'required|exists:kategori_nama_tabungans,id',
            'jenis' => 'required|exists:kategori_jenis_tabungans,id',
            'nominal' => 'required|numeric|min:0',
            'keterangan' => 'required|string|max:255',
            'jatuh_tempo' => 'required|date',
        ]);

        $validated['user_id'] = auth()->id();
        $validated['family_id'] = auth()->user()->family_id;
        
        PlannedTransaction::create($validated);

        return redirect()->route('planned-transactions.index')->with('success', 'Rencana transaksi berhasil ditambahkan.');
    }

    public function edit(PlannedTransaction $plannedTransaction)
    {
        // Permission check (Allow family members)
        // For now keep it simple or check if both in same family
        
        $namaKategori = KategoriNamaTabungan::all();
        $jenisKategori = KategoriJenisTabungan::all();
        return view('planned-transactions.edit', compact('plannedTransaction', 'namaKategori', 'jenisKategori'));
    }

    public function update(Request $request, PlannedTransaction $plannedTransaction)
    {
        $validated = $request->validate([
            'nama' => 'required|exists:kategori_nama_tabungans,id',
            'jenis' => 'required|exists:kategori_jenis_tabungans,id',
            'nominal' => 'required|numeric|min:0',
            'keterangan' => 'required|string|max:255',
            'jatuh_tempo' => 'required|date',
            'tanggal_peristiwa' => 'nullable|date',
        ]);

        $plannedTransaction->update($validated);

        return redirect()->route('planned-transactions.index')->with('success', 'Rencana transaksi berhasil diupdate.');
    }

    public function destroy(PlannedTransaction $plannedTransaction)
    {
        $plannedTransaction->delete();
        return redirect()->route('planned-transactions.index')->with('success', 'Rencana transaksi berhasil dihapus.');
    }

    public function complete(Request $request, PlannedTransaction $plannedTransaction)
    {
        $request->validate(['tanggal_peristiwa' => 'required|date']);

        Tabungan::create([
            'nama' => $plannedTransaction->nama,
            'jenis' => $plannedTransaction->jenis,
            'nominal' => $plannedTransaction->nominal,
            'keterangan' => $plannedTransaction->keterangan . " (Realisasi dari rencana)",
            'user_id' => auth()->id(),
            'family_id' => auth()->user()->family_id,
            'created_at' => $request->tanggal_peristiwa,
            'updated_at' => $request->tanggal_peristiwa,
        ]);

        $plannedTransaction->update([
            'status' => 'done',
            'tanggal_peristiwa' => $request->tanggal_peristiwa,
        ]);

        return redirect()->route('planned-transactions.index')->with('success', 'Rencana transaksi berhasil direalisasikan!');
    }
}