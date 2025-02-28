<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\User;
use App\Models\CustomerSetting;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rules;
use Carbon\Carbon;

class CustomerController extends Controller
{
    /**
     * Hiển thị danh sách khách hàng của đại lý
     */
    public function index()
    {
        $customers = Auth::user()->customers()->orderBy('created_at', 'desc')->paginate(20);
        return view('customers.index', compact('customers'));
    }

    /**
     * Hiển thị form tạo khách hàng mới
     */
    public function create()
    {
        return view('customers.create');
    }

    /**
     * Lưu thông tin khách hàng mới
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'phone' => ['nullable', 'string', 'max:20'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'note' => ['nullable', 'string'],
            
            // Cài đặt chung
            'is_sync_enabled' => ['nullable', 'boolean'],
            'cashback_all' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'cashback_north' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'cashback_central' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'cashback_south' => ['nullable', 'numeric', 'min:0', 'max:100'],
            
            // Cài đặt miền Nam
            'south_head_tail_rate' => ['nullable', 'numeric', 'min:0'],
            'south_lo_rate' => ['nullable', 'numeric', 'min:0'],
            'south_3_digits_rate' => ['nullable', 'numeric', 'min:0'],
            'south_3_head_tail_rate' => ['nullable', 'numeric', 'min:0'],
            'south_4_digits_rate' => ['nullable', 'numeric', 'min:0'],
            'south_slide_rate' => ['nullable', 'numeric', 'min:0'],
            'south_straight_rate' => ['nullable', 'numeric', 'min:0'],
            'south_head_tail_win' => ['nullable', 'numeric', 'min:0'],
            'south_lo_win' => ['nullable', 'numeric', 'min:0'],
            'south_3_digits_win' => ['nullable', 'numeric', 'min:0'],
            'south_3_head_tail_win' => ['nullable', 'numeric', 'min:0'],
            'south_4_digits_win' => ['nullable', 'numeric', 'min:0'],
            'south_slide_win' => ['nullable', 'numeric', 'min:0'],
            'south_straight_win' => ['nullable', 'numeric', 'min:0'],
            'south_straight_bonus' => ['nullable', 'boolean'],
            'south_straight_win_type' => ['nullable', 'integer', 'in:1,2,3'],
            'south_slide_win_type' => ['nullable', 'integer', 'in:1,2,3'],
            
            // Cài đặt miền Bắc
            'north_head_tail_rate' => ['nullable', 'numeric', 'min:0'],
            'north_lo_rate' => ['nullable', 'numeric', 'min:0'],
            'north_3_digits_rate' => ['nullable', 'numeric', 'min:0'],
            'north_3_head_tail_rate' => ['nullable', 'numeric', 'min:0'],
            'north_4_digits_rate' => ['nullable', 'numeric', 'min:0'],
            'north_slide_rate' => ['nullable', 'numeric', 'min:0'],
            'north_head_tail_win' => ['nullable', 'numeric', 'min:0'],
            'north_lo_win' => ['nullable', 'numeric', 'min:0'],
            'north_3_digits_win' => ['nullable', 'numeric', 'min:0'],
            'north_3_head_tail_win' => ['nullable', 'numeric', 'min:0'],
            'north_4_digits_win' => ['nullable', 'numeric', 'min:0'],
            'north_slide_win' => ['nullable', 'numeric', 'min:0'],
            'north_straight_bonus' => ['nullable', 'boolean'],
            'north_slide_win_type' => ['nullable', 'integer', 'in:1,2,3'],
            'north_slide2_rate' => ['nullable', 'numeric', 'min:0'],
            'north_slide2_win' => ['nullable', 'numeric', 'min:0'],
            'north_slide3_rate' => ['nullable', 'numeric', 'min:0'],
            'north_slide3_win' => ['nullable', 'numeric', 'min:0'],
            'north_slide4_rate' => ['nullable', 'numeric', 'min:0'],
            'north_slide4_win' => ['nullable', 'numeric', 'min:0'],
            'north_slide5_rate' => ['nullable', 'numeric', 'min:0'],
            'north_slide5_win' => ['nullable', 'numeric', 'min:0'],
            'north_slide6_rate' => ['nullable', 'numeric', 'min:0'],
            'north_slide6_win' => ['nullable', 'numeric', 'min:0'],
            
            // Cài đặt miền Trung
            'central_head_tail_rate' => ['nullable', 'numeric', 'min:0'],
            'central_lo_rate' => ['nullable', 'numeric', 'min:0'],
            'central_3_digits_rate' => ['nullable', 'numeric', 'min:0'],
            'central_3_head_tail_rate' => ['nullable', 'numeric', 'min:0'],
            'central_4_digits_rate' => ['nullable', 'numeric', 'min:0'],
            'central_slide_rate' => ['nullable', 'numeric', 'min:0'],
            'central_straight_rate' => ['nullable', 'numeric', 'min:0'],
            'central_head_tail_win' => ['nullable', 'numeric', 'min:0'],
            'central_lo_win' => ['nullable', 'numeric', 'min:0'],
            'central_3_digits_win' => ['nullable', 'numeric', 'min:0'],
            'central_3_head_tail_win' => ['nullable', 'numeric', 'min:0'],
            'central_4_digits_win' => ['nullable', 'numeric', 'min:0'],
            'central_slide_win' => ['nullable', 'numeric', 'min:0'],
            'central_straight_win' => ['nullable', 'numeric', 'min:0'],
            'central_straight_bonus' => ['nullable', 'boolean'],
            'central_straight_win_type' => ['nullable', 'integer', 'in:1,2,3'],
            'central_slide_win_type' => ['nullable', 'integer', 'in:1,2,3'],
        ]);

        $customerRole = Role::where('name', 'Customer')->first();
        if (!$customerRole) {
            return back()->with('error', 'Không tìm thấy role Customer');
        }

        // Tạo giao dịch trong database transaction để đảm bảo toàn vẹn dữ liệu
        DB::beginTransaction();
        
        try {
            // Tạo người dùng mới
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'phone' => $validated['phone'] ?? null,
                'password' => Hash::make($validated['password']),
                'role_id' => $customerRole->id,
                'agent_id' => Auth::id(),
                'balance' => 0,
                'settings' => [
                    'note' => $validated['note'] ?? '',
                ],
            ]);

            // Trích xuất tất cả cài đặt từ request
            $settingsData = array_filter($request->all(), function($key) use ($request) {
                return in_array($key, (new CustomerSetting())->getFillable());
            }, ARRAY_FILTER_USE_KEY);
            
            // Chuyển đổi checkbox thành boolean
            $settingsData['is_sync_enabled'] = $request->has('is_sync_enabled');
            $settingsData['south_straight_bonus'] = $request->has('south_straight_bonus');
            $settingsData['north_straight_bonus'] = $request->has('north_straight_bonus');
            $settingsData['central_straight_bonus'] = $request->has('central_straight_bonus');
            
            // Thêm user_id vào cài đặt
            $settingsData['user_id'] = $user->id;
            
            // Tạo cài đặt
            CustomerSetting::create($settingsData);
            
            DB::commit();
            
            return redirect()->route('customers.index')
                ->with('success', 'Thêm khách hàng thành công');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Đã xảy ra lỗi: ' . $e->getMessage());
        }
    }

    /**
     * Hiển thị thông tin chi tiết của khách hàng
     */
    public function show(User $customer)
    {
        // Kiểm tra quyền truy cập
        if ($customer->agent_id !== Auth::id()) {
            abort(403, 'Bạn không có quyền xem khách hàng này');
        }

        // Thống kê
        $totalBets = $customer->bets()->count();
        $wonBets = $customer->bets()->where('is_won', true)->count();
        $lostBets = $customer->bets()->where('is_won', false)->count();
        $pendingBets = $customer->bets()->whereNull('is_won')->count();
        
        $totalSpent = $customer->bets()->sum('amount');
        $totalWon = $customer->bets()->where('is_won', true)->sum('win_amount');
        
        // Lấy các vé cược gần đây
        $recentBets = $customer->bets()->with(['betType', 'region', 'province'])
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();
            
        // Lấy các giao dịch gần đây
        $recentTransactions = $customer->transactions()
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        return view('customers.show', compact(
            'customer', 
            'totalBets', 
            'wonBets', 
            'lostBets', 
            'pendingBets',
            'totalSpent',
            'totalWon',
            'recentBets',
            'recentTransactions'
        ));
    }

    /**
     * Hiển thị form chỉnh sửa khách hàng
     */
    public function edit(User $customer)
    {
        // Kiểm tra quyền truy cập
        if ($customer->agent_id !== Auth::id()) {
            abort(403, 'Bạn không có quyền chỉnh sửa khách hàng này');
        }

        // Đảm bảo khách hàng đã có bản ghi cài đặt
        if (!$customer->setting) {
            $customer->setting()->create([]);
        }

        return view('customers.edit', compact('customer'));
    }

    /**
     * Cập nhật thông tin khách hàng
     */
    public function update(Request $request, User $customer)
    {
        // Kiểm tra quyền truy cập
        if ($customer->agent_id !== Auth::id()) {
            abort(403, 'Bạn không có quyền chỉnh sửa khách hàng này');
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,' . $customer->id],
            'phone' => ['nullable', 'string', 'max:20'],
            'password' => ['nullable', 'confirmed', Rules\Password::defaults()],
            'note' => ['nullable', 'string'],
            
            // Cài đặt chung và các cài đặt khác như trong phương thức store
            // Cùng các validation rule như trong phương thức store
        ]);

        DB::beginTransaction();
        
        try {
            // Cập nhật thông tin người dùng
            $updateData = [
                'name' => $validated['name'],
                'email' => $validated['email'],
                'phone' => $validated['phone'] ?? null,
                'settings' => array_merge($customer->settings ?? [], [
                    'note' => $validated['note'] ?? '',
                ]),
            ];

            if (!empty($validated['password'])) {
                $updateData['password'] = Hash::make($validated['password']);
            }

            $customer->update($updateData);
            
            // Trích xuất tất cả cài đặt từ request
            $settingsData = array_filter($request->all(), function($key) use ($request) {
                return in_array($key, (new CustomerSetting())->getFillable());
            }, ARRAY_FILTER_USE_KEY);
            
            // Chuyển đổi checkbox thành boolean
            $settingsData['is_sync_enabled'] = $request->has('is_sync_enabled');
            $settingsData['south_straight_bonus'] = $request->has('south_straight_bonus');
            $settingsData['north_straight_bonus'] = $request->has('north_straight_bonus');
            $settingsData['central_straight_bonus'] = $request->has('central_straight_bonus');
            
            // Cập nhật hoặc tạo mới cài đặt
            if ($customer->setting) {
                $customer->setting->update($settingsData);
            } else {
                $settingsData['user_id'] = $customer->id;
                CustomerSetting::create($settingsData);
            }
            
            DB::commit();
            
            return redirect()->route('customers.show', $customer)
                ->with('success', 'Cập nhật khách hàng thành công');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Đã xảy ra lỗi: ' . $e->getMessage());
        }
    }

    /**
     * Điều chỉnh số dư khách hàng
     */
    public function adjustBalance(Request $request, User $customer)
    {
        // Kiểm tra quyền truy cập
        if ($customer->agent_id !== Auth::id()) {
            abort(403, 'Bạn không có quyền điều chỉnh số dư của khách hàng này');
        }

        $validated = $request->validate([
            'amount' => ['required', 'numeric'],
            'description' => ['required', 'string', 'max:255'],
        ]);

        $amount = $validated['amount'];
        $description = $validated['description'];

        // Kiểm tra nếu giảm tiền, không được giảm quá số dư hiện có
        if ($amount < 0 && abs($amount) > $customer->balance) {
            return back()->with('error', 'Số tiền rút không được lớn hơn số dư hiện có');
        }

        DB::beginTransaction();
        
        try {
            // Lưu trạng thái trước khi thay đổi
            $balanceBefore = $customer->balance;
            $balanceAfter = $balanceBefore + $amount;

            // Tạo transaction
            Transaction::create([
                'user_id' => $customer->id,
                'type' => $amount > 0 ? 'deposit' : 'withdrawal',
                'amount' => $amount,
                'balance_before' => $balanceBefore,
                'balance_after' => $balanceAfter,
                'description' => $description,
            ]);

            // Cập nhật số dư
            $customer->balance = $balanceAfter;
            $customer->save();
            
            DB::commit();

            return redirect()->route('customers.show', $customer)
                ->with('success', 'Điều chỉnh số dư thành công');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Đã xảy ra lỗi: ' . $e->getMessage());
        }
    }

    /**
     * Hiển thị form đặt cược cho khách hàng
     */
    public function betForCustomer(User $customer)
    {
        // Kiểm tra quyền truy cập
        if ($customer->agent_id !== Auth::id()) {
            abort(403, 'Bạn không có quyền đặt cược cho khách hàng này');
        }

        // Lấy danh sách tỉnh đài mở thưởng hôm nay
        $today = Carbon::now();
        $dayOfWeek = $today->dayOfWeek;
        
        $todayProvinces = \App\Models\Province::where('draw_day', $dayOfWeek)
            ->where('is_active', true)
            ->orderBy('region_id')
            ->orderBy('name')
            ->get();

        return view('customers.bet', compact('customer', 'todayProvinces'));
    }
    
    /**
     * Xem lịch sử cược của khách hàng
     */
    public function bets(User $customer, Request $request)
    {
        // Kiểm tra quyền truy cập
        if ($customer->agent_id !== Auth::id()) {
            abort(403, 'Bạn không có quyền xem lịch sử cược của khách hàng này');
        }
        
        $query = $customer->bets();
        
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
        
        // Sắp xếp
        $query->orderBy('created_at', 'desc');
        
        $bets = $query->with(['betType', 'region', 'province'])->paginate(20)->withQueryString();
        
        return view('customers.bets', compact('customer', 'bets'));
    }
    
    /**
     * Xem lịch sử giao dịch của khách hàng
     */
    public function transactions(User $customer, Request $request)
    {
        // Kiểm tra quyền truy cập
        if ($customer->agent_id !== Auth::id()) {
            abort(403, 'Bạn không có quyền xem lịch sử giao dịch của khách hàng này');
        }
        
        $query = $customer->transactions();
        
        // Lọc theo ngày
        if ($request->filled('from_date')) {
            $query->whereDate('created_at', '>=', $request->from_date);
        }
        
        if ($request->filled('to_date')) {
            $query->whereDate('created_at', '<=', $request->to_date);
        }
        
        // Lọc theo loại giao dịch
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }
        
        // Sắp xếp
        $query->orderBy('created_at', 'desc');
        
        $transactions = $query->paginate(20)->withQueryString();
        
        return view('customers.transactions', compact('customer', 'transactions'));
    }
    
    /**
     * Xóa khách hàng (chỉ có thể xóa khi khách hàng chưa có giao dịch nào)
     */
    public function destroy(User $customer)
    {
        // Kiểm tra quyền truy cập
        if ($customer->agent_id !== Auth::id()) {
            abort(403, 'Bạn không có quyền xóa khách hàng này');
        }
        
        // Kiểm tra xem khách hàng đã có giao dịch chưa
        if ($customer->bets()->exists() || $customer->transactions()->exists()) {
            return back()->with('error', 'Không thể xóa khách hàng đã có giao dịch');
        }
        
        DB::beginTransaction();
        
        try {
            // Xóa cài đặt của khách hàng
            if ($customer->setting) {
                $customer->setting->delete();
            }
            
            // Xóa khách hàng
            $customer->delete();
            
            DB::commit();
            
            return redirect()->route('customers.index')
                ->with('success', 'Xóa khách hàng thành công');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Đã xảy ra lỗi: ' . $e->getMessage());
        }
    }
}