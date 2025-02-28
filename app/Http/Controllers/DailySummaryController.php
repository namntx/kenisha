<?php

namespace App\Http\Controllers;

use App\Models\Bet;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DailySummaryController extends Controller
{
    /**
     * Hiển thị tổng kết ngày
     */
    public function index(Request $request)
    {
        // Lấy ngày từ request, mặc định là hôm nay
        $date = $request->input('date') ? Carbon::parse($request->input('date')) : Carbon::today();
        
        // Lấy customer_id nếu được chọn
        $customerId = $request->input('customer_id');
        
        // Lấy danh sách khách hàng của agent hiện tại
        $customers = Auth::user()->customers;
        
        // Query cơ bản
        $query = Bet::query()
            ->whereDate('bet_date', $date)
            ->with(['betType', 'region', 'province', 'user']);
        
        // Nếu là Agent, chỉ lấy vé của các khách hàng của họ
        if (Auth::user()->isAgent()) {
            $customerIds = $customers->pluck('id')->toArray();
            $query->whereIn('user_id', $customerIds);
            
            // Lọc theo khách hàng cụ thể nếu được chọn
            if ($customerId) {
                $query->where('user_id', $customerId);
            }
        }
        
        // Lấy danh sách vé cược
        $bets = $query->orderBy('created_at')->get();
        
        // Tính toán tổng tiền thắng/thua
        $totalBetAmount = $bets->sum('amount');
        $totalWonAmount = $bets->where('is_won', true)->sum('win_amount');
        $netProfit = $totalWonAmount - $totalBetAmount;
        
        // Nhóm vé theo khách hàng
        $betsByCustomer = $bets->groupBy('user_id');
        $summaryByCustomer = [];
        
        foreach ($betsByCustomer as $userId => $customerBets) {
            $customer = $customers->firstWhere('id', $userId);
            if ($customer) {
                $betAmount = $customerBets->sum('amount');
                $wonAmount = $customerBets->where('is_won', true)->sum('win_amount');
                $profit = $wonAmount - $betAmount;
                
                $summaryByCustomer[] = [
                    'customer' => $customer,
                    'bets_count' => $customerBets->count(),
                    'bet_amount' => $betAmount,
                    'won_amount' => $wonAmount,
                    'profit' => $profit
                ];
            }
        }
        
        return view('daily-summary.index', compact(
            'date',
            'bets',
            'customers',
            'customerId',
            'totalBetAmount',
            'totalWonAmount',
            'netProfit',
            'summaryByCustomer'
        ));
    }
    
    /**
     * Hiển thị chi tiết tổng kết theo khách hàng
     */
    public function customer(Request $request, User $customer)
    {
        // Kiểm tra quyền truy cập
        if ($customer->agent_id !== Auth::id()) {
            abort(403, 'Bạn không có quyền xem thông tin của khách hàng này');
        }
        
        // Lấy ngày từ request, mặc định là hôm nay
        $date = $request->input('date') ? Carbon::parse($request->input('date')) : Carbon::today();
        
        // Lấy danh sách vé cược của khách hàng trong ngày
        $bets = Bet::where('user_id', $customer->id)
            ->whereDate('bet_date', $date)
            ->with(['betType', 'region', 'province'])
            ->orderBy('created_at')
            ->get();
        
        // Tính toán tổng tiền thắng/thua
        $totalBetAmount = $bets->sum('amount');
        $totalWonAmount = $bets->where('is_won', true)->sum('win_amount');
        $netProfit = $totalWonAmount - $totalBetAmount;
        
        // Nhóm theo ngày của tháng hiện tại để tạo lịch sử
        $startOfMonth = Carbon::now()->startOfMonth();
        $endOfMonth = Carbon::now()->endOfMonth();
        
        $monthlyHistory = Bet::where('user_id', $customer->id)
            ->whereBetween('bet_date', [$startOfMonth, $endOfMonth])
            ->select(
                DB::raw('DATE(bet_date) as date'),
                DB::raw('SUM(amount) as total_bet'),
                DB::raw('SUM(CASE WHEN is_won = 1 THEN win_amount ELSE 0 END) as total_won')
            )
            ->groupBy('date')
            ->orderBy('date', 'desc')
            ->get()
            ->map(function ($item) {
                $item->net_profit = $item->total_won - $item->total_bet;
                return $item;
            });
        
        return view('daily-summary.customer', compact(
            'customer',
            'date',
            'bets',
            'totalBetAmount',
            'totalWonAmount',
            'netProfit',
            'monthlyHistory'
        ));
    }
}