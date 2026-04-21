<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\KategoriNamaTabungan;

class KategoriNamaTabunganController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $data = KategoriNamaTabungan::all();
        return view('kategori.nama-index', compact('data'));
    }

    public function create()
    {
        return view('kategori.nama-create');
    }

    // Menyimpan kategori baru setelah validasi duplikat
    public function store(Request $request)
    {
        $request->validate([
            'nama' => 'required|string|max:255|unique:kategori_nama_tabungans,nama',
            'kategori_kas' => 'nullable|string',
            'target_saldo' => 'nullable|numeric',
            'icon' => 'nullable|string',
            'wallet_type' => 'nullable|string|in:pos,savings',
            'status' => 'nullable|string',
            'color' => 'nullable|string',
        ]);

        KategoriNamaTabungan::create([
            'nama' => $request->nama,
            'kategori_kas' => $request->kategori_kas,
            'target_saldo' => $request->target_saldo,
            'icon' => $request->icon ?? 'account_balance',
            'wallet_type' => $request->wallet_type ?? 'pos',
            'status' => $request->status ?? 'AKTIF',
            'color' => $request->color ?? '#006d36',
        ]);

        return redirect()->route('wallet.index')->with('success', 'Dompet baru berhasil diaktifkan');
    }

    // Menampilkan form edit
    public function edit($id)
    {
        $kategori = KategoriNamaTabungan::findOrFail($id);
        return view('kategori.nama.edit', compact('kategori'));
    }

    // Memperbarui data kategori setelah validasi duplikat
    public function update(Request $request, $id)
    {
        $request->validate([
            'nama' => 'required|string|max:255|unique:kategori_nama_tabungans,nama,' . $id,
        ]);

        $kategori = KategoriNamaTabungan::findOrFail($id);
        $kategori->update([
            'nama' => $request->nama
        ]);

        return redirect()->route('kategori.nama.index')->with('success', 'Nama Kategori berhasil diupdate');
    }

    // Menghapus kategori tabungan
    public function destroy($id)
    {
        $kategori = KategoriNamaTabungan::findOrFail($id);
        $kategori->delete();

        return redirect()->route('kategori.nama.index')->with('success', 'Nama Kategori berhasil dihapus');
    }
}
