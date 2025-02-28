<?php

namespace App\Http\Controllers;

use App\Models\LotteryResult;
use App\Models\Province;
use App\Models\Region;
use App\Services\LotteryProcessor;
use Illuminate\Http\Request;
use Carbon\Carbon;

class LotteryResultController extends Controller
{
    protected $lotteryProcessor;
    
    public function __construct(LotteryProcessor $lotteryProcessor)
    {
        $this->lotteryProcessor = $lotteryProcessor;
    }
    
    public function index(Request $request)
    {
        $query = LotteryResult::query();
        
        // Lọc theo ngày
        if ($request->filled('date')) {
            $query->whereDate('draw_date', $request->date);
        }
        
        // Lọc theo vùng miền
        if ($request->filled('region_id')) {
            $query->where('region_id', $request->region_id);
        }
        
        // Lọc theo tỉnh
        if ($request->filled('province_id')) {
            $query->where('province_id', $request->province_id);
        }
        
        // Sắp xếp theo ngày mới nhất
        $results = $query->orderBy('draw_date', 'desc')
                         ->orderBy('created_at', 'desc')
                         ->with(['region', 'province'])
                         ->paginate(20)
                         ->withQueryString();
        
        // Lấy danh sách vùng miền và tỉnh cho dropdown lọc
        $regions = Region::all();
        $provinces = Province::all();
        
        return view('lottery-results.index', compact('results', 'regions', 'provinces'));
    }
    
    public function create()
    {
        $regions = Region::all();
        $provinces = Province::all();
        
        // Lấy ngày hiện tại
        $today = Carbon::now()->format('Y-m-d');
        
        return view('lottery-results.create', compact('regions', 'provinces', 'today'));
    }
    
    public function store(Request $request)
    {
        $validated = $request->validate([
            'region_id' => 'required|exists:regions,id',
            'province_id' => 'nullable|exists:provinces,id',
            'draw_date' => 'required|date',
            'results' => 'required|array',
        ]);
        
        // Kiểm tra xem kết quả đã tồn tại chưa
        $existingResult = LotteryResult::where('draw_date', $validated['draw_date'])
                                      ->where('region_id', $validated['region_id']);
        
        if (isset($validated['province_id'])) {
            $existingResult->where('province_id', $validated['province_id']);
        } else {
            $existingResult->whereNull('province_id');
        }
        
        if ($existingResult->exists()) {
            return back()->with('error', 'Kết quả xổ số cho ngày và tỉnh/khu vực này đã tồn tại');
        }
        
        // Lưu kết quả
        $result = new LotteryResult();
        $result->region_id = $validated['region_id'];
        $result->province_id = $validated['province_id'] ?? null;
        $result->draw_date = $validated['draw_date'];
        $result->results = json_encode($validated['results']);
        $result->is_processed = false;
        $result->save();
        
        return redirect()->route('lottery-results.index')
                        ->with('success', 'Thêm kết quả xổ số thành công');
    }
    
    public function show(LotteryResult $lotteryResult)
    {
        return view('lottery-results.show', compact('lotteryResult'));
    }
    
    public function edit(LotteryResult $lotteryResult)
    {
        // Không cho phép chỉnh sửa kết quả đã xử lý
        if ($lotteryResult->is_processed) {
            return back()->with('error', 'Không thể chỉnh sửa kết quả đã xử lý');
        }
        
        $regions = Region::all();
        $provinces = Province::all();
        
        // Chuyển kết quả từ JSON sang mảng
        $results = json_decode($lotteryResult->results, true);
        
        return view('lottery-results.edit', compact('lotteryResult', 'regions', 'provinces', 'results'));
    }
    
    public function update(Request $request, LotteryResult $lotteryResult)
    {
        // Không cho phép cập nhật kết quả đã xử lý
        if ($lotteryResult->is_processed) {
            return back()->with('error', 'Không thể cập nhật kết quả đã xử lý');
        }
        
        $validated = $request->validate([
            'region_id' => 'required|exists:regions,id',
            'province_id' => 'nullable|exists:provinces,id',
            'draw_date' => 'required|date',
            'results' => 'required|array',
        ]);
        
        // Cập nhật kết quả
        $lotteryResult->region_id = $validated['region_id'];
        $lotteryResult->province_id = $validated['province_id'] ?? null;
        $lotteryResult->draw_date = $validated['draw_date'];
        $lotteryResult->results = json_encode($validated['results']);
        $lotteryResult->save();
        
        return redirect()->route('lottery-results.index')
                        ->with('success', 'Cập nhật kết quả xổ số thành công');
    }
    
    public function destroy(LotteryResult $lotteryResult)
    {
        // Không cho phép xóa kết quả đã xử lý
        if ($lotteryResult->is_processed) {
            return back()->with('error', 'Không thể xóa kết quả đã xử lý');
        }
        
        $lotteryResult->delete();
        
        return redirect()->route('lottery-results.index')
                        ->with('success', 'Xóa kết quả xổ số thành công');
    }
    
    /**
     * Xử lý kết quả xổ số
     */
    public function process(LotteryResult $lotteryResult)
    {
        // Không cho phép xử lý lại kết quả đã xử lý
        if ($lotteryResult->is_processed) {
            return back()->with('error', 'Kết quả này đã được xử lý trước đó');
        }
        
        // Sử dụng LotteryProcessor để xử lý kết quả
        $result = $this->lotteryProcessor->processResult($lotteryResult);
        
        if ($result['status'] === 'success') {
            return back()->with('success', $result['message']);
        } else {
            return back()->with('error', $result['message']);
        }
    }
}