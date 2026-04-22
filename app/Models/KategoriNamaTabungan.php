<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KategoriNamaTabungan extends Model
{
    use HasFactory;

    protected static function booted(): void
    {
        static::addGlobalScope(new \App\Models\Scopes\GroupScope);
    }

    protected $fillable = [
        'nama',
        'kategori_kas',
        'target_saldo',
        'icon',
        'group_id',
        'wallet_type',
        'status',
        'color',
        'is_group',
        'user_id',
        'reminder_limit',
        'is_reminder_active',
        'target_date',
        'description',
        'image_url',
        'milestones',
    ];

    protected $appends = ['balance'];

    public function group()
    {
        return $this->belongsTo(Group::class, 'group_id');
    }

    public function owner()
    {
        return $this->belongsTo(User::class, 'user_id');
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
