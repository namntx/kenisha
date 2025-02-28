<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomerSetting extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'user_id',
        'is_sync_enabled',
        'cashback_all',
        'cashback_north',
        'cashback_central',
        'cashback_south',
        
        // Miền Nam
        'south_head_tail_rate',
        'south_lo_rate',
        'south_3_digits_rate',
        'south_3_head_tail_rate',
        'south_4_digits_rate',
        'south_slide_rate',
        'south_straight_rate',
        'south_head_tail_win',
        'south_lo_win',
        'south_3_digits_win',
        'south_3_head_tail_win',
        'south_4_digits_win',
        'south_slide_win',
        'south_straight_win',
        'south_straight_bonus',
        'south_straight_win_type',
        'south_slide_win_type',
        
        // Miền Bắc
        'north_head_tail_rate',
        'north_lo_rate',
        'north_3_digits_rate',
        'north_3_head_tail_rate',
        'north_4_digits_rate',
        'north_slide_rate',
        'north_head_tail_win',
        'north_lo_win',
        'north_3_digits_win',
        'north_3_head_tail_win',
        'north_4_digits_win',
        'north_slide_win',
        'north_straight_bonus',
        'north_slide_win_type',
        'north_slide2_rate',
        'north_slide2_win',
        'north_slide3_rate',
        'north_slide3_win',
        'north_slide4_rate',
        'north_slide4_win',
        'north_slide5_rate',
        'north_slide5_win',
        'north_slide6_rate',
        'north_slide6_win',
        
        // Miền Trung
        'central_head_tail_rate',
        'central_lo_rate',
        'central_3_digits_rate',
        'central_3_head_tail_rate',
        'central_4_digits_rate',
        'central_slide_rate',
        'central_straight_rate',
        'central_head_tail_win',
        'central_lo_win',
        'central_3_digits_win',
        'central_3_head_tail_win',
        'central_4_digits_win',
        'central_slide_win',
        'central_straight_win',
        'central_straight_bonus',
        'central_straight_win_type',
        'central_slide_win_type',
    ];
    
    protected $casts = [
        'is_sync_enabled' => 'boolean',
        'cashback_all' => 'decimal:2',
        'cashback_north' => 'decimal:2',
        'cashback_central' => 'decimal:2',
        'cashback_south' => 'decimal:2',
        
        // Tỷ lệ thu
        'south_head_tail_rate' => 'decimal:3',
        'south_lo_rate' => 'decimal:3',
        'south_3_digits_rate' => 'decimal:3',
        'south_3_head_tail_rate' => 'decimal:3',
        'south_4_digits_rate' => 'decimal:3',
        'south_slide_rate' => 'decimal:3',
        'south_straight_rate' => 'decimal:3',
        
        'north_head_tail_rate' => 'decimal:3',
        'north_lo_rate' => 'decimal:3',
        'north_3_digits_rate' => 'decimal:3',
        'north_3_head_tail_rate' => 'decimal:3',
        'north_4_digits_rate' => 'decimal:3',
        'north_slide_rate' => 'decimal:3',
        'north_slide2_rate' => 'decimal:3',
        'north_slide3_rate' => 'decimal:3',
        'north_slide4_rate' => 'decimal:3',
        'north_slide5_rate' => 'decimal:3',
        'north_slide6_rate' => 'decimal:3',
        
        'central_head_tail_rate' => 'decimal:3',
        'central_lo_rate' => 'decimal:3',
        'central_3_digits_rate' => 'decimal:3',
        'central_3_head_tail_rate' => 'decimal:3',
        'central_4_digits_rate' => 'decimal:3',
        'central_slide_rate' => 'decimal:3',
        'central_straight_rate' => 'decimal:3',
        
        // Lần ăn
        'south_head_tail_win' => 'decimal:3',
        'south_lo_win' => 'decimal:3',
        'south_3_digits_win' => 'decimal:3',
        'south_3_head_tail_win' => 'decimal:3',
        'south_4_digits_win' => 'decimal:3',
        'south_slide_win' => 'decimal:3',
        'south_straight_win' => 'decimal:3',
        
        'north_head_tail_win' => 'decimal:3',
        'north_lo_win' => 'decimal:3',
        'north_3_digits_win' => 'decimal:3',
        'north_3_head_tail_win' => 'decimal:3',
        'north_4_digits_win' => 'decimal:3',
        'north_slide_win' => 'decimal:3',
        'north_slide2_win' => 'decimal:3',
        'north_slide3_win' => 'decimal:3',
        'north_slide4_win' => 'decimal:3',
        'north_slide5_win' => 'decimal:3',
        'north_slide6_win' => 'decimal:3',
        
        'central_head_tail_win' => 'decimal:3',
        'central_lo_win' => 'decimal:3',
        'central_3_digits_win' => 'decimal:3',
        'central_3_head_tail_win' => 'decimal:3',
        'central_4_digits_win' => 'decimal:3',
        'central_slide_win' => 'decimal:3',
        'central_straight_win' => 'decimal:3',
        
        'south_straight_bonus' => 'boolean',
        'north_straight_bonus' => 'boolean',
        'central_straight_bonus' => 'boolean',
    ];
    
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}