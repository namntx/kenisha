<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Province extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'name',
        'code',
        'region_id',
        'draw_day',
        'is_active'
    ];
    
    public function region()
    {
        return $this->belongsTo(Region::class);
    }
    
    public function lotteryResults()
    {
        return $this->hasMany(LotteryResult::class);
    }
    
    public function bets()
    {
        return $this->hasMany(Bet::class);
    }
    
    // Helper để lấy tên ngày trong tuần
    public function getDayOfWeekNameAttribute()
    {
        $days = [
            0 => 'Chủ nhật',
            1 => 'Thứ hai',
            2 => 'Thứ ba',
            3 => 'Thứ tư',
            4 => 'Thứ năm',
            5 => 'Thứ sáu',
            6 => 'Thứ bảy'
        ];
        
        return $days[$this->draw_day] ?? '';
    }
}