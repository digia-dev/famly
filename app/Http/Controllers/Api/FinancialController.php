<?php

namespace App\Http\Controllers\Api;

use App\Models\Tabungan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class FinancialController extends ApiController
{
    public function index()
    {
        // By default, it will use the FamilyScope established in the booted method
        $transactions = Tabungan::with(['user', 'kategoriNama', 'kategoriJenis'])->latest()->get();
        return $this->success($transactions);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nama' => 'required|integer', // foreign key to kategori_nama_tabungans
            'jenis' => 'required|integer', // foreign key to kategori_jenis_tabungans
            'nominal' => 'required|numeric',
            'keterangan' => 'nullable|string',
            'user_id' => 'required|exists:users,id',
            'status' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return $this->error($validator->errors()->first(), 422);
        }

        $transaction = Tabungan::create($request->all());

        \App\Models\ActivityLog::create([
            'user_id' => auth()->id() ?? 1,
            'action' => 'CREATE_TXN',
            'entity_type' => 'Financial',
            'entity_id' => $transaction->id,
            'metadata' => ['amount' => $transaction->nominal, 'note' => $transaction->keterangan]
        ]);

        return $this->success($transaction, 'Transaction created successfully', 201);

    }

    public function show($id)
    {
        $transaction = Tabungan::with(['user', 'kategoriNama', 'kategoriJenis'])->find($id);
        if (!$transaction) {
            return $this->error('Transaction not found', 404);
        }
        return $this->success($transaction);
    }

    public function update(Request $request, $id)
    {
        $transaction = Tabungan::find($id);
        if (!$transaction) {
            return $this->error('Transaction not found', 404);
        }

        $validator = Validator::make($request->all(), [
            'nama' => 'integer',
            'jenis' => 'integer',
            'nominal' => 'numeric',
            'keterangan' => 'nullable|string',
            'status' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return $this->error($validator->errors()->first(), 422);
        }

        $transaction->update($request->all());

        \App\Models\ActivityLog::create([
            'user_id' => auth()->id() ?? 1,
            'action' => 'UPDATE_TXN',
            'entity_type' => 'Financial',
            'entity_id' => $transaction->id,
            'metadata' => ['changes' => $request->all()]
        ]);

        return $this->success($transaction, 'Transaction updated successfully');

    }

    public function destroy($id)
    {
        $transaction = Tabungan::find($id);
        if (!$transaction) {
            return $this->error('Transaction not found', 404);
        }
        $transaction->delete();

        \App\Models\ActivityLog::create([
            'user_id' => auth()->id() ?? 1,
            'action' => 'DELETE_TXN',
            'entity_type' => 'Financial',
            'entity_id' => $id,
            'metadata' => ['note' => $transaction->keterangan]
        ]);

        return $this->success(null, 'Transaction deleted successfully');

    }
}
