<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Family extends Model
{
    use HasFactory;

    protected $fillable = ['family_name'];

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function wallets()
    {
        return $this->hasMany(KategoriNamaTabungan::class, 'family_id');
    }

    public function tasks()
    {
        return $this->hasMany(PlannedTransaction::class, 'family_id');
    }
}
