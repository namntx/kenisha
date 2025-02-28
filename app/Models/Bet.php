<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Bet extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'user_id',
        'region_id',
        'province_id',
        'bet_type_id',
        'bet_date',
        'numbers',
        'amount',
        'collection_rate',
        'win_multiplier',
        'collected_amount',
        'potential_win',
        'is_won',
        'win_amount',
        'is_processed',
        'raw_input'
    ];
    
    protected $casts = [
        'bet_date' => 'date',
        'is_won' => 'boolean',
        'is_processed' => 'boolean',
        'amount' => 'decimal:2',
        'collection_rate' => 'decimal:4',
        'win_multiplier' => 'decimal:2',
        'collected_amount' => 'decimal:2',
        'potential_win' => 'decimal:2',
        'win_amount' => 'decimal:2',
    ];
    
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    
    public function region()
    {
        return $this->belongsTo(Region::class);
    }
    
    public function province()
    {
        return $this->belongsTo(Province::class);
    }
    
    public function betType()
    {
        return $this->belongsTo(BetType::class);
    }
    
    public function transaction()
    {
        return $this->hasOne(Transaction::class);
    }
    
    // Helper để hiển thị tỉnh hoặc khu vực
    public function getLocationNameAttribute()
    {
        return $this->province ? $this->province->name : $this->region->name;
    }
}