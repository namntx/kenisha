<?php

namespace App\Http\Controllers;

use App\Models\LotteryResult;
use App\Models\Province;
use App\Models\Region;
use App\Services\LotteryApiService;
use App\Services\LotteryProcessorService;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class LotteryResultController extends Controller
{
    protected $lotteryProcessor;
    protected $lotteryApiService;
    
    public function __construct(LotteryProcessorService $lotteryProcessor, LotteryApiService $lotteryApiService)
    {
        $this->lotteryProcessor = $lotteryProcessor;
        $this->lotteryApiService = $lotteryApiService;
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
        
        return view('lottery-results.create', compact('regions', 'provinces'));
    }
    
    public function store(Request $request)
    {
        $validated = $request->validate([
            'region_id' => 'required|exists:regions,id',
            'province_id' => 'nullable|exists:provinces,id',
            'draw_date' => 'required|date',
            'results' => 'required|array',
        ]);
        
        // Kiểm tra xem đã có kết quả cho ngày và tỉnh/khu vực này chưa
        $existingResult = LotteryResult::where('draw_date', $validated['draw_date'])
            ->where('region_id', $validated['region_id']);
            
        if ($validated['province_id']) {
            $existingResult->where('province_id', $validated['province_id']);
        } else {
            $existingResult->whereNull('province_id');
        }
        
        if ($existingResult->exists()) {
            return redirect()->back()->with('error', 'Kết quả cho ngày và tỉnh/khu vực này đã tồn tại');
        }
        
        // Tạo kết quả mới
        $result = LotteryResult::create($validated);
        
        return redirect()->route('lottery-results.index')
            ->with('success', 'Kết quả xổ số đã được thêm thành công');
    }
    
    public function show(LotteryResult $lotteryResult)
    {
        return view('lottery-results.show', compact('lotteryResult'));
    }
    
    public function edit(LotteryResult $lotteryResult)
    {
        $regions = Region::all();
        $provinces = Province::all();
        
        return view('lottery-results.edit', compact('lotteryResult', 'regions', 'provinces'));
    }
    
    public function update(Request $request, LotteryResult $lotteryResult)
    {
        $validated = $request->validate([
            'region_id' => 'required|exists:regions,id',
            'province_id' => 'nullable|exists:provinces,id',
            'draw_date' => 'required|date',
            'results' => 'required|array',
        ]);
        
        $lotteryResult->update($validated);
        
        return redirect()->route('lottery-results.index')
            ->with('success', 'Kết quả xổ số đã được cập nhật thành công');
    }
    
    public function destroy(LotteryResult $lotteryResult)
    {
        $lotteryResult->delete();
        
        return redirect()->route('lottery-results.index')
            ->with('success', 'Kết quả xổ số đã được xóa thành công');
    }
    
    public function process($id)
    {
        $result = LotteryResult::findOrFail($id);
        
        if ($result->is_processed) {
            return response()->json([
                'status' => 'error',
                'message' => 'Kết quả này đã được xử lý trước đó'
            ]);
        }
        
        try {
            $processResult = $this->lotteryProcessor->processResult($result);
            return response()->json($processResult);
        } catch (\Exception $e) {
            Log::error('Lỗi xử lý kết quả: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Đã xảy ra lỗi: ' . $e->getMessage()
            ], 500);
        }
    }
    
    public function fetchResults(Request $request)
    {
        $validated = $request->validate([
            'date' => 'required|date',
            'region' => 'required|string|in:mb,mt,mn',
            'province' => 'nullable|string',
            'auto_process' => 'nullable|boolean'
        ]);
        
        $date = Carbon::parse($validated['date']);
        $region = $validated['region'];
        $province = $validated['province'] ?? null;
        $autoProcess = $validated['auto_process'] ?? false;
        
        try {
            // Lấy kết quả từ API
            $results = $this->lotteryApiService->getResults($date, $region, $province);
            
            if (!$results) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Không tìm thấy kết quả cho ngày và khu vực đã chọn'
                ]);
            }
            
            // Lưu kết quả vào cơ sở dữ liệu
            $regionModel = Region::where('code', $region)->first();
            $provinceModel = null;
            
            if ($province) {
                $provinceModel = Province::where('code', $province)->first();
            }
            
            if (!$regionModel) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Không tìm thấy khu vực ' . $region
                ]);
            }
            
            // Kiểm tra xem đã có kết quả cho ngày và tỉnh/khu vực này chưa
            $existingResult = LotteryResult::where('draw_date', $date->format('Y-m-d'))
                ->where('region_id', $regionModel->id);
                
            if ($provinceModel) {
                $existingResult->where('province_id', $provinceModel->id);
            } else {
                $existingResult->whereNull('province_id');
            }
            
            $existingResult = $existingResult->first();
            
            if ($existingResult) {
                // Cập nhật kết quả hiện có
                $existingResult->results = $results;
                $existingResult->save();
                $lotteryResult = $existingResult;
                
                $message = 'Kết quả đã được cập nhật thành công';
            } else {
                // Tạo kết quả mới
                $lotteryResult = LotteryResult::create([
                    'region_id' => $regionModel->id,
                    'province_id' => $provinceModel ? $provinceModel->id : null,
                    'draw_date' => $date->format('Y-m-d'),
                    'results' => $results,
                    'is_processed' => false
                ]);
                
                $message = 'Kết quả đã được lưu thành công';
            }
            
            // Xử lý kết quả nếu được yêu cầu
            if ($autoProcess) {
                $processResult = $this->lotteryProcessor->processResult($lotteryResult);
                return response()->json([
                    'status' => 'success',
                    'message' => $message . ' và đã được xử lý',
                    'process_result' => $processResult
                ]);
            }
            
            return response()->json([
                'status' => 'success',
                'message' => $message
            ]);
            
        } catch (\Exception $e) {
            Log::error('Lỗi lấy kết quả: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Đã xảy ra lỗi: ' . $e->getMessage()
            ], 500);
        }
    }
}
