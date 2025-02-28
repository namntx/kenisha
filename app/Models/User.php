<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'password',
        'role_id',
        'agent_id',
        'balance',
        'settings',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'balance' => 'decimal:2',
        'settings' => 'array',
    ];

    public function setting()
    {
        return $this->hasOne(CustomerSetting::class);
    }

    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    public function agent()
    {
        return $this->belongsTo(User::class, 'agent_id');
    }

    public function customers()
    {
        return $this->hasMany(User::class, 'agent_id');
    }

    public function bets()
    {
        return $this->hasMany(Bet::class);
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    // Helper methods
    public function isAdmin()
    {
        return $this->role->name === 'Admin';
    }

    public function isAgent()
    {
        return $this->role->name === 'Agent';
    }

    public function isCustomer()
    {
        return $this->role->name === 'Customer';
    }

    // Ensure a setting record exists for this user
    public function getSettingAttribute()
    {
        $setting = $this->setting()->first();
        
        if (!$setting && $this->isCustomer()) {
            // Create default settings
            $setting = $this->setting()->create([]);
        }
        
        return $setting;
    }
}