<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class BetType extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'code',
        'name',
        'description',
        'payout_ratio',
        'syntax_pattern',
        'example',
        'is_active'
    ];
    
    protected $casts = [
        'payout_ratio' => 'decimal:2',
        'is_active' => 'boolean',
    ];
    
    public function bets()
    {
        return $this->hasMany(Bet::class);
    }
}
