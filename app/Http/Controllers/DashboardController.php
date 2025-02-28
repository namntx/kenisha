<?php

namespace App\Http\Controllers;

use App\Models\Bet;
use App\Models\Province;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $today = Carbon::now();
        $dayOfWeek = $today->dayOfWeek;
        
        // Lấy danh sách tỉnh đài hôm nay
        $todayProvinces = Province::where('draw_day', $dayOfWeek)
            ->where('is_active', true)
            ->orderBy('region_id')
            ->orderBy('name')
            ->get();
            
        return view('dashboard', compact(
            'user',
            'todayProvinces'
        ));
    }
}