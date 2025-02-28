<?php

namespace App\Http\Controllers;

use App\Models\Bet;
use App\Models\Province;
use App\Models\Region;
use App\Models\Transaction;
use App\Services\BetParserService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class BetController extends Controller
{
    protected $betParserService;
    
    public function __construct(BetParserService $betParserService)
    {
        $this->betParserService = $betParserService;
    }

    public function index(Request $request)
    {
        $query = Bet::where('user_id', Auth::id());
        
        // Lọc theo ngày
        if ($request->filled('from_date')) {
            $query->where('bet_date', '>=', $request->from_date);
        }
        
        if ($request->filled('to_date')) {
            $query->where('bet_date', '<=', $request->to_date);
        }
        
        // Lọc theo trạng thái
        if ($request->filled('status')) {
            if ($request->status === 'pending') {
                $query->where('is_processed', false);
            } elseif ($request->status === 'won') {
                $query->where('is_processed', true)->where('is_won', true);
            } elseif ($request->status === 'lost') {
                $query->where('is_processed', true)->where('is_won', false);
            }
        }
        
        $bets = $query->orderBy('created_at', 'desc')->paginate(20)->withQueryString();
        
        return view('bets.index', compact('bets'));
    }
    
    // Các phương thức khác giữ nguyên...
    
    public function parse(Request $request)
    {
        $input = $request->input('bet_string');
        $betDate = $request->input('bet_date') ? Carbon::parse($request->input('bet_date')) : Carbon::now();
        $result = $this->betParserService->parse($input, $betDate);
        
        return response()->json($result);
    }

    // Thêm phương thức mới vào BetController
    public function storeForCustomer(Request $request, User $customer)
    {
        // Kiểm tra quyền truy cập
        if ($customer->agent_id !== Auth::id()) {
            abort(403, 'Bạn không có quyền đặt cược cho khách hàng này');
        }

        $validated = $request->validate([
            'bet_string' => 'required|string',
            'bet_date' => 'nullable|date',
        ]);
        
        $betDate = isset($validated['bet_date']) ? Carbon::parse($validated['bet_date']) : Carbon::now();
        
        // Truyền $customer vào phương thức parse để sử dụng cài đặt cá nhân
        $parsed = $this->betParserService->parse($validated['bet_string'], $betDate, $customer);
        
        if (!$parsed['is_valid']) {
            return back()->with('error', 'Cú pháp không hợp lệ: ' . $parsed['error']);
        }
        
        // Kiểm tra số dư của khách hàng
        $totalAmount = $parsed['amount'] * count($parsed['numbers']);
        if ($customer->balance < $totalAmount) {
            return back()->with('error', 'Số dư của khách hàng không đủ để đặt cược: '.number_format($customer->balance).' < '.number_format($totalAmount));
        }
        
        // Tạo giao dịch trong transaction
        DB::beginTransaction();
        
        try {
            foreach ($parsed['numbers'] as $number) {
                $bet = new Bet();
                $bet->user_id = $customer->id; // Đặt user_id là ID của khách hàng
                $bet->region_id = $parsed['region_id'];
                
                // Thêm province_id nếu có
                if (isset($parsed['province_id'])) {
                    $bet->province_id = $parsed['province_id'];
                }
                
                $bet->bet_type_id = $parsed['bet_type_id'];
                $bet->bet_date = $betDate->format('Y-m-d');
                $bet->numbers = $number;
                $bet->amount = $parsed['amount'];
                $bet->potential_win = $parsed['potential_win'];
                $bet->raw_input = $parsed['raw_input'];
                $bet->save();
                
                // Xác định tên tỉnh/khu vực để hiển thị trong mô tả
                $locationName = '';
                if (isset($parsed['province_id'])) {
                    $province = Province::find($parsed['province_id']);
                    $locationName = $province->name;
                } else {
                    $region = Region::find($parsed['region_id']);
                    $locationName = $region->name;
                }
                
                // Ghi transaction
                $transaction = new Transaction();
                $transaction->user_id = $customer->id;
                $transaction->type = 'bet';
                $transaction->amount = -$parsed['amount'];
                $transaction->balance_before = $customer->balance;
                $transaction->balance_after = $customer->balance - $parsed['amount'];
                $transaction->bet_id = $bet->id;
                $transaction->description = "Đặt cược {$parsed['type']} {$number} {$locationName}";
                $transaction->save();
                
                // Cập nhật số dư của khách hàng
                $customer->balance -= $parsed['amount'];
                $customer->save();
            }
            
            DB::commit();
            return redirect()->route('customers.show', $customer)
                ->with('success', 'Đặt cược thành công cho khách hàng');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Đã xảy ra lỗi: ' . $e->getMessage());
        }
    }
    
    public function store(Request $request)
    {
        $validated = $request->validate([
            'bet_string' => 'required|string',
            'bet_date' => 'nullable|date',
        ]);
        
        $betDate = isset($validated['bet_date']) ? Carbon::parse($validated['bet_date']) : Carbon::now();
        $parsed = $this->betParserService->parse($validated['bet_string'], $betDate);
        
        if (!$parsed['is_valid']) {
            return back()->with('error', 'Cú pháp không hợp lệ: ' . $parsed['error']);
        }
        
        $user = Auth::user();
        
        // Kiểm tra số dư
        if ($user->balance < $parsed['amount'] * count($parsed['numbers'])) {
            return back()->with('error', 'Số dư không đủ để đặt cược');
        }
        
        // Tạo giao dịch trong transaction
        DB::beginTransaction();
        
        try {
            foreach ($parsed['numbers'] as $number) {
                $bet = new Bet();
                $bet->user_id = $user->id;
                $bet->region_id = $parsed['region_id'];
                
                // Thêm province_id nếu có
                if (isset($parsed['province_id'])) {
                    $bet->province_id = $parsed['province_id'];
                }
                
                $bet->bet_type_id = $parsed['bet_type_id'];
                $bet->bet_date = $betDate->format('Y-m-d');
                $bet->numbers = $number;
                $bet->amount = $parsed['amount'];
                $bet->potential_win = $parsed['potential_win'];
                $bet->raw_input = $parsed['raw_input'];
                $bet->save();
                
                // Xác định tên tỉnh/khu vực để hiển thị trong mô tả
                $locationName = '';
                if (isset($parsed['province_id'])) {
                    $province = Province::find($parsed['province_id']);
                    $locationName = $province->name;
                } else {
                    $region = Region::find($parsed['region_id']);
                    $locationName = $region->name;
                }
                
                // Ghi transaction
                $transaction = new Transaction();
                $transaction->user_id = $user->id;
                $transaction->type = 'bet';
                $transaction->amount = -$parsed['amount'];
                $transaction->balance_before = $user->balance;
                $transaction->balance_after = $user->balance - $parsed['amount'];
                $transaction->bet_id = $bet->id;
                $transaction->description = "Đặt cược {$parsed['type']} {$number} {$locationName}";
                $transaction->save();
                
                // Cập nhật số dư
                $user->balance -= $parsed['amount'];
                $user->save();
            }
            
            DB::commit();
            return redirect()->route('bets.index')->with('success', 'Đặt cược thành công');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Đã xảy ra lỗi: ' . $e->getMessage());
        }
    }
    
    // Thêm phương thức để lấy danh sách tỉnh đài theo ngày
    public function getProvincesByDate(Request $request)
    {
        $date = $request->input('date') ? Carbon::parse($request->input('date')) : Carbon::now();
        $dayOfWeek = $date->dayOfWeek;
        
        $provinces = Province::where('draw_day', $dayOfWeek)
            ->where('is_active', true)
            ->orderBy('region_id')
            ->orderBy('name')
            ->get();
            
        return response()->json($provinces);
    }
}