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
            
            // Và các cài đặt tương tự cho miền Bắc và miền Trung...
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
                'balance' => 0, // Vẫn giữ trường này để tương thích, nhưng không sử dụng
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

        return view('customers.show', compact(
            'customer', 
            'totalBets', 
            'wonBets', 
            'lostBets', 
            'pendingBets',
            'totalSpent',
            'totalWon',
            'recentBets'
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
            
            // Và các cài đặt khác như trong phương thức store
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
     * Xóa khách hàng (chỉ có thể xóa khi khách hàng chưa có giao dịch nào)
     */
    public function destroy(User $customer)
    {
        // Kiểm tra quyền truy cập
        if ($customer->agent_id !== Auth::id()) {
            abort(403, 'Bạn không có quyền xóa khách hàng này');
        }
        
        // Kiểm tra xem khách hàng đã có giao dịch chưa
        if ($customer->bets()->exists()) {
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
}