<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KategoriNamaTabungan extends Model
{
    use HasFactory;

    protected static function booted(): void
    {
        static::addGlobalScope(new \App\Models\Scopes\FamilyScope);
    }

    protected $fillable = [
        'nama',
        'kategori_kas',
        'target_saldo',
        'icon',
        'family_id',
        'wallet_type',
        'status',
        'color',
    ];

    protected $appends = ['balance'];

    public function family()
    {
        return $this->belongsTo(Family::class);
    }

    public function transactions()
    {
        return $this->hasMany(Tabungan::class, 'nama');
    }

    public function getBalanceAttribute()
    {
        // Calculate based on linked transactions
        $income = $this->transactions()->whereHas('kategoriJenis', function($q) {
            $q->where('jenis', 'Pemasukan');
        })->sum('nominal');

        $expense = $this->transactions()->whereHas('kategoriJenis', function($q) {
            $q->where('jenis', 'Pengeluaran');
        })->sum('nominal');

        return $income - $expense;
    }
}
