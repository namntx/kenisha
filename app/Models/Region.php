<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Region extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'name',
        'code',
        'description'
    ];
    
    public function provinces()
    {
        return $this->hasMany(Province::class);
    }
    
    public function lotteryResults()
    {
        return $this->hasMany(LotteryResult::class);
    }
    
    public function bets()
    {
        return $this->hasMany(Bet::class);
    }
}