<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Group extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'type', 'invite_code', 'admin_id', 'is_paid'];

    /**
     * Get the members of the group.
     */
    public function members()
    {
        return $this->belongsToMany(User::class, 'group_members')
                    ->withPivot(['role', 'status'])
                    ->withTimestamps();
    }

    /**
     * Get the wallets for the group.
     */
    public function wallets()
    {
        return $this->hasMany(KategoriNamaTabungan::class, 'group_id');
    }

    /**
     * Get the planned transactions for the group.
     */
    public function tasks()
    {
        return $this->hasMany(PlannedTransaction::class, 'group_id');
    }

    /**
     * Get the chat messages for the group.
     */
    public function messages()
    {
        return $this->hasMany(ChatMessage::class, 'group_id');
    }

    /**
     * Check if a user is an admin of this group.
     */
    public function isAdmin($userId)
    {
        return $this->members()
            ->where('user_id', $userId)
            ->wherePivot('role', 'admin')
            ->exists();
    }

    /**
     * Check if a user is a member (regular or admin) of this group.
     */
    public function isMember($userId)
    {
        return $this->members()
            ->where('user_id', $userId)
            ->wherePivotIn('status', ['Active'])
            ->exists();
    }
}
