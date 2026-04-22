<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Relations\HasMany;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'group_id', 
        'current_group_id',
        'role',
        'subscription_status',
        'subscription_until',
        'ai_usage_count',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'subscription_until' => 'datetime',
    ];

    public function tabungan()
    {
        return $this->hasMany(Tabungan::class);
    }

    public function groups()
    {
        return $this->belongsToMany(Group::class, 'group_members')
                    ->withPivot(['role', 'status'])
                    ->withTimestamps();
    }

    public function currentGroup()
    {
        return $this->belongsTo(Group::class, 'current_group_id');
    }

    public function wallets()
    {
        return $this->hasMany(KategoriNamaTabungan::class, 'user_id');
    }

    public function familyRoles()
    {
        return $this->hasMany(FamilyRole::class);
    }

    public function activityLogs()
    {
        return $this->hasMany(ActivityLog::class);
    }

    /**
     * Check if user is a premium subscriber.
     */
    public function isPremium(): bool
    {
        return $this->subscription_status === 'subscriber' && 
               ($this->subscription_until === null || $this->subscription_until > now());
    }

    public function notifications()
    {
        return $this->hasMany(Notification::class);
    }
}

