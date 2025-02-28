<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LotteryResult extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'region_id',
        'province_id',
        'draw_date',
        'results',
        'is_processed'
    ];
    
    protected $casts = [
        'draw_date' => 'date',
        'results' => 'array',
        'is_processed' => 'boolean'
    ];
    
    public function region()
    {
        return $this->belongsTo(Region::class);
    }
    
    public function province()
    {
        return $this->belongsTo(Province::class);
    }
    
    // Helper để hiển thị tỉnh hoặc khu vực
    public function getLocationNameAttribute()
    {
        return $this->province ? $this->province->name : $this->region->name;
    }
}