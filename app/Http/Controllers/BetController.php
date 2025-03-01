<?php

namespace App\Http\Controllers;

use App\Models\Bet;
use App\Models\Province;
use App\Models\Region;
use App\Models\LotteryResult;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Services\BetParserService;
use App\Services\LotteryProcessorService;

class BetController extends Controller
{
    protected $betParserService;
    protected $lotteryProcessor;
    
    public function __construct(BetParserService $betParserService, LotteryProcessorService $lotteryProcessor)
    {
        $this->betParserService = $betParserService;
        $this->lotteryProcessor = $lotteryProcessor;
    }

    public function index(Request $request)
    {
        $query = Bet::query();
        
        // Nếu là Agent, chỉ lấy vé của các khách hàng của họ
        if (Auth::user()->isAgent()) {
            $customerIds = Auth::user()->customers()->pluck('id')->toArray();
            $query->whereIn('user_id', $customerIds);
        }
        
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
        
        $bets = $query->with(['betType', 'region', 'province', 'user'])
            ->orderBy('created_at', 'desc')
            ->paginate(20)
            ->withQueryString();
        
        return view('bets.index', compact('bets'));
    }
    
    public function parse(Request $request)
    {
        $input = $request->input('bet_string');
        $betDate = $request->input('bet_date') ? Carbon::parse($request->input('bet_date')) : Carbon::now();
        
        // Lấy đối tượng User từ ID
        $customerId = $request->input('customer');
        $customer = null;
        
        if ($customerId) {
            $customer = \App\Models\User::find($customerId);
        }
        
        $result = $this->betParserService->parse($input, $betDate, $customer);
        
        return response()->json($result);
    }

    public function storeForCustomer(Request $request, $customer)
    {
        // Lấy thông tin khách hàng
        $customer = \App\Models\User::find($customer);

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
        
        // Tạo giao dịch trong transaction
        DB::beginTransaction();
        
        try {
            $savedBets = [];
            
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
                
                // Lưu thông tin tỷ lệ thu, lần ăn và tiền thu
                if (isset($parsed['collection_rate'])) {
                    $bet->collection_rate = $parsed['collection_rate'];
                }
                if (isset($parsed['win_multiplier'])) {
                    $bet->win_multiplier = $parsed['win_multiplier'];
                }
                if (isset($parsed['collected_amount'])) {
                    $bet->collected_amount = $parsed['collected_amount'];
                }
                
                $bet->potential_win = $parsed['potential_win'];
                $bet->raw_input = $parsed['raw_input'];
                $bet->save();
                
                $savedBets[] = $bet;
                
                // Xác định tên tỉnh/khu vực để hiển thị trong mô tả
                $locationName = '';
                if (isset($parsed['province_id'])) {
                    $province = Province::find($parsed['province_id']);
                    $locationName = $province->name;
                } else {
                    $region = Region::find($parsed['region_id']);
                    $locationName = $region->name;
                }
            }
            
            // Kiểm tra nếu đã có kết quả xổ số cho ngày đặt cược, tự động xử lý vé
            $this->processNewBets($savedBets);
            
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
        
        // Tạo giao dịch trong transaction
        DB::beginTransaction();
        
        try {
            $savedBets = [];
            
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
                
                // Lưu thông tin tỷ lệ thu, lần ăn và tiền thu
                if (isset($parsed['collection_rate'])) {
                    $bet->collection_rate = $parsed['collection_rate'];
                }
                if (isset($parsed['win_multiplier'])) {
                    $bet->win_multiplier = $parsed['win_multiplier'];
                }
                if (isset($parsed['collected_amount'])) {
                    $bet->collected_amount = $parsed['collected_amount'];
                }
                
                $bet->potential_win = $parsed['potential_win'];
                $bet->raw_input = $parsed['raw_input'];
                $bet->save();
                
                $savedBets[] = $bet;
                
                // Xác định tên tỉnh/khu vực để hiển thị trong mô tả
                $locationName = '';
                if (isset($parsed['province_id'])) {
                    $province = Province::find($parsed['province_id']);
                    $locationName = $province->name;
                } else {
                    $region = Region::find($parsed['region_id']);
                    $locationName = $region->name;
                }
            }
            
            // Kiểm tra nếu đã có kết quả xổ số cho ngày đặt cược, tự động xử lý vé
            $this->processNewBets($savedBets);
            
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
    
    /**
     * Xử lý các vé cược mới nếu đã có kết quả xổ số
     * 
     * @param array $bets Danh sách các vé cược cần xử lý
     * @return void
     */
    private function processNewBets(array $bets)
    {
        if (empty($bets)) {
            return;
        }
        
        // Lấy thông tin từ vé cược đầu tiên
        $firstBet = $bets[0];
        $betDate = $firstBet->bet_date;
        $regionId = $firstBet->region_id;
        $provinceId = $firstBet->province_id;
        
        // Tìm kết quả xổ số tương ứng
        $query = LotteryResult::where('draw_date', $betDate);
        
        if ($provinceId) {
            // Nếu có province_id, tìm kết quả theo province_id
            $query->where('province_id', $provinceId);
        } else {
            // Nếu không có province_id, tìm kết quả theo region_id
            $query->where('region_id', $regionId)->whereNull('province_id');
        }
        
        $result = $query->first();
        
        // Nếu đã có kết quả xổ số, xử lý các vé cược
        if ($result && !empty($result->results)) {
            foreach ($bets as $bet) {
                $this->lotteryProcessor->processBet($bet, $result);
            }
            
            // Cập nhật trạng thái đã xử lý của kết quả xổ số nếu chưa được xử lý
            if (!$result->is_processed) {
                $result->is_processed = true;
                $result->save();
            }
        }
    }
    
    /**
     * Xử lý tất cả các vé cược đang chờ
     * 
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function processAllPendingBets(Request $request)
    {
        // Lấy tất cả các vé cược chưa xử lý
        $pendingBets = Bet::where('is_processed', false)->get();
        
        if ($pendingBets->isEmpty()) {
            return response()->json([
                'status' => 'success',
                'message' => 'Không có vé cược nào đang chờ xử lý'
            ]);
        }
        
        // Nhóm các vé cược theo ngày, vùng miền và tỉnh
        $groupedBets = [];
        foreach ($pendingBets as $bet) {
            $key = $bet->bet_date . '_' . $bet->region_id . '_' . ($bet->province_id ?? 'null');
            if (!isset($groupedBets[$key])) {
                $groupedBets[$key] = [
                    'bet_date' => $bet->bet_date,
                    'region_id' => $bet->region_id,
                    'province_id' => $bet->province_id,
                    'bets' => []
                ];
            }
            $groupedBets[$key]['bets'][] = $bet;
        }
        
        $processedCount = 0;
        $results = [];
        
        // Xử lý từng nhóm vé cược
        foreach ($groupedBets as $key => $group) {
            // Tìm kết quả xổ số tương ứng
            $query = LotteryResult::where('draw_date', $group['bet_date'])
                ->where('region_id', $group['region_id']);
            
            if ($group['province_id']) {
                $query->where('province_id', $group['province_id']);
            } else {
                $query->whereNull('province_id');
            }
            
            $result = $query->first();
            
            // Nếu có kết quả xổ số, xử lý các vé cược
            if ($result && !empty($result->results)) {
                $regionName = Region::find($group['region_id'])->name;
                $provinceName = $group['province_id'] ? Province::find($group['province_id'])->name : null;
                $locationName = $provinceName ?? $regionName;
                
                foreach ($group['bets'] as $bet) {
                    $this->lotteryProcessor->processBet($bet, $result);
                    $processedCount++;
                }
                
                $results[] = [
                    'location' => $locationName,
                    'date' => $group['bet_date'],
                    'processed_count' => count($group['bets'])
                ];
                
                // Cập nhật trạng thái đã xử lý của kết quả xổ số nếu chưa được xử lý
                if (!$result->is_processed) {
                    $result->is_processed = true;
                    $result->save();
                }
            }
        }
        
        return response()->json([
            'status' => 'success',
            'message' => 'Đã xử lý ' . $processedCount . ' vé cược đang chờ',
            'processed_count' => $processedCount,
            'details' => $results
        ]);
    }
}