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
            
        // Số lượng khách hàng (nếu là Agent)
        $customersCount = $user->isAgent() ? $user->customers()->count() : 0;
        
        // Số lượng vé cược của khách hàng (nếu là Agent)
        $betsCount = 0;
        if ($user->isAgent()) {
            $customerIds = $user->customers()->pluck('id');
            $betsCount = Bet::whereIn('user_id', $customerIds)->count();
        } else {
            $betsCount = $user->bets()->count();
        }
        
        // Tổng số tiền thắng/thua trong ngày hôm nay
        $todaySummary = $this->getTodaySummary($user);
        
        // Khách hàng mới nhất (nếu là Agent)
        $latestCustomers = $user->isAgent() ? $user->customers()->latest()->take(5)->get() : collect();
        
        // Vé cược gần đây
        $recentBets = $this->getRecentBets($user);
            
        return view('dashboard', compact(
            'user',
            'todayProvinces',
            'customersCount',
            'betsCount',
            'todaySummary',
            'latestCustomers',
            'recentBets'
        ));
    }
    
    /**
     * Lấy tổng hợp thắng/thua trong ngày hôm nay
     */
    private function getTodaySummary($user)
    {
        $today = Carbon::today();
        
        $query = Bet::whereDate('bet_date', $today);
        
        // Nếu là Agent, lấy vé của tất cả khách hàng
        if ($user->isAgent()) {
            $customerIds = $user->customers()->pluck('id');
            $query->whereIn('user_id', $customerIds);
        } else {
            $query->where('user_id', $user->id);
        }
        
        $totalBet = $query->sum('amount');
        $totalWon = $query->where('is_won', true)->sum('win_amount');
        $netProfit = $totalWon - $totalBet;
        
        return [
            'total_bet' => $totalBet,
            'total_won' => $totalWon,
            'net_profit' => $netProfit
        ];
    }
    
    /**
     * Lấy danh sách vé cược gần đây
     */
    private function getRecentBets($user)
    {
        // Nếu là Agent, lấy vé của tất cả khách hàng
        if ($user->isAgent()) {
            $customerIds = $user->customers()->pluck('id');
            return Bet::whereIn('user_id', $customerIds)
                ->with(['user', 'betType', 'region', 'province'])
                ->orderBy('created_at', 'desc')
                ->take(5)
                ->get();
        } else {
            return $user->bets()
                ->with(['betType', 'region', 'province'])
                ->orderBy('created_at', 'desc')
                ->take(5)
                ->get();
        }
    }
}