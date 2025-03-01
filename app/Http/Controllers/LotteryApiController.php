<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Province;
use App\Models\Region;
use App\Models\LotteryResult;
use App\Services\LotteryApiService;
use App\Services\LotteryProcessorService;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class LotteryApiController extends Controller
{
    protected $lotteryApiService;
    protected $lotteryProcessor;
    
    public function __construct(LotteryApiService $lotteryApiService, LotteryProcessorService $lotteryProcessor)
    {
        $this->lotteryApiService = $lotteryApiService;
        $this->lotteryProcessor = $lotteryProcessor;
    }
    
    /**
     * Lấy kết quả xổ số từ API và lưu vào cơ sở dữ liệu
     */
    public function fetchResults(Request $request)
    {
        $request->validate([
            'date' => 'nullable|date',
            'region' => 'required|string|in:mb,mn,mt',
            'province' => 'nullable|string',
        ]);
        
        $date = $request->input('date') ? Carbon::parse($request->input('date')) : Carbon::now();
        $region = $request->input('region');
        $province = $request->input('province');
        
        try {
            // Tìm region model
            $regionModel = Region::where('code', $region)->first();
            
            if (!$regionModel) {
                return response()->json(['status' => 'error', 'message' => 'Không tìm thấy vùng miền ' . $region]);
            }
            
            // Nếu có tỉnh, kiểm tra tỉnh
            if ($province) {
                $provinceModel = Province::where('code', $province)
                    ->where('region_id', $regionModel->id)
                    ->first();
                
                if (!$provinceModel) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Không tìm thấy tỉnh ' . $province . ' trong vùng miền ' . $region
                    ]);
                }
                
                // Lấy kết quả cho tỉnh cụ thể
                $results = $this->lotteryApiService->getResults($date, $region, $province);
                
                if (!$results) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Không thể lấy kết quả xổ số cho ngày ' . $date->format('d/m/Y')
                    ]);
                }
                
                // Kiểm tra xem kết quả đã tồn tại chưa
                $existingResult = LotteryResult::where('draw_date', $date->format('Y-m-d'))
                    ->where('region_id', $regionModel->id)
                    ->where('province_id', $provinceModel->id)
                    ->first();
                
                if ($existingResult) {
                    // Cập nhật kết quả nếu chưa được xử lý
                    if (!$existingResult->is_processed) {
                        $existingResult->results = json_encode($results);
                        $existingResult->save();
                    }
                    
                    // Xử lý các vé cược đang chờ cho kết quả này
                    $processResult = $this->lotteryProcessor->processResult($existingResult);
                    
                    return response()->json([
                        'status' => 'success',
                        'message' => 'Kết quả đã tồn tại',
                        'data' => json_decode($existingResult->results),
                        'is_processed' => $existingResult->is_processed,
                        'processing_result' => $processResult
                    ]);
                } else {
                    // Lưu kết quả mới
                    $lotteryResult = new LotteryResult();
                    $lotteryResult->region_id = $regionModel->id;
                    $lotteryResult->province_id = $provinceModel->id;
                    $lotteryResult->draw_date = $date->format('Y-m-d');
                    $lotteryResult->results = json_encode($results);
                    $lotteryResult->save();
                    
                    // Xử lý các vé cược đang chờ cho kết quả này
                    $processResult = $this->lotteryProcessor->processResult($lotteryResult);
                    
                    return response()->json([
                        'status' => 'success',
                        'message' => 'Đã lấy kết quả xổ số thành công',
                        'data' => $results,
                        'is_processed' => false,
                        'processing_result' => $processResult
                    ]);
                }
            } else {
                // Lấy kết quả cho cả vùng miền
                $results = $this->lotteryApiService->getResults($date, $region);
                
                if (!$results) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Không thể lấy kết quả xổ số cho ngày ' . $date->format('d/m/Y')
                    ]);
                }
                
                // Kiểm tra xem kết quả đã tồn tại chưa
                $existingResult = LotteryResult::where('draw_date', $date->format('Y-m-d'))
                    ->where('region_id', $regionModel->id)
                    ->whereNull('province_id')
                    ->first();
                
                if ($existingResult) {
                    // Cập nhật kết quả nếu chưa được xử lý
                    if (!$existingResult->is_processed) {
                        $existingResult->results = json_encode($results);
                        $existingResult->save();
                    }
                    
                    // Xử lý các vé cược đang chờ cho kết quả này
                    $processResult = $this->lotteryProcessor->processResult($existingResult);
                    
                    return response()->json([
                        'status' => 'success',
                        'message' => 'Kết quả đã tồn tại',
                        'data' => json_decode($existingResult->results),
                        'is_processed' => $existingResult->is_processed,
                        'processing_result' => $processResult
                    ]);
                } else {
                    // Lưu kết quả mới
                    $lotteryResult = new LotteryResult();
                    $lotteryResult->region_id = $regionModel->id;
                    $lotteryResult->province_id = null;
                    $lotteryResult->draw_date = $date->format('Y-m-d');
                    $lotteryResult->results = json_encode($results);
                    $lotteryResult->save();
                    
                    // Xử lý các vé cược đang chờ cho kết quả này
                    $processResult = $this->lotteryProcessor->processResult($lotteryResult);
                    
                    return response()->json([
                        'status' => 'success',
                        'message' => 'Đã lấy kết quả xổ số thành công',
                        'data' => $results,
                        'is_processed' => false,
                        'processing_result' => $processResult
                    ]);
                }
            }
        } catch (\Exception $e) {
            Log::error('Error in fetchResults: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Đã xảy ra lỗi: ' . $e->getMessage()
            ]);
        }
    }
    
    /**
     * Lấy kết quả xổ số từ API nhưng không lưu vào cơ sở dữ liệu
     */
    public function getResults(Request $request)
    {
        $request->validate([
            'date' => 'nullable|date',
            'region' => 'required|string|in:mb,mn,mt',
            'province' => 'nullable|string',
            'format' => 'nullable|string'
        ]);
        
        $date = $request->input('date') ? Carbon::parse($request->input('date')) : Carbon::now();
        $region = $request->input('region');
        $province = $request->input('province');
        $returnJson = $request->input('format') === 'json';
        
        try {
            // Lấy kết quả từ API
            $results = $this->lotteryApiService->getResults($date, $region, $province);
            
            if (!$results) {
                if ($returnJson) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Không thể lấy kết quả xổ số cho ngày ' . $date->format('d/m/Y')
                    ], 404);
                } else {
                    return redirect()->route('lottery-results.index')->with('error', 'Không thể lấy kết quả xổ số cho ngày ' . $date->format('d/m/Y'));
                }
            }
            
            if ($returnJson) {
                return response()->json([
                    'status' => 'success',
                    'message' => 'Lấy kết quả xổ số thành công',
                    'data' => $results
                ]);
            } else {
                return redirect()->route('lottery-results.index', [
                    'date' => $date->format('Y-m-d')
                ])->with([
                    'status' => 'success',
                    'message' => 'Lấy kết quả xổ số thành công',
                    'data' => $results
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Error in getResults: ' . $e->getMessage(), [
                'date' => $date->format('Y-m-d'),
                'region' => $region,
                'province' => $province,
                'exception' => $e
            ]);
            
            if ($returnJson) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Đã xảy ra lỗi: ' . $e->getMessage()
                ], 500);
            } else {
                return redirect()->route('lottery-results.index')->with('error', 'Đã xảy ra lỗi: ' . $e->getMessage());
            }
        }
    }
    
    /**
     * Lấy kết quả và cập nhật tự động cho tất cả tỉnh trong ngày
     */
    public function fetchAllResults(Request $request)
    {
        $request->validate([
            'date' => 'nullable|date',
        ]);
        
        $date = $request->input('date') ? Carbon::parse($request->input('date')) : Carbon::now();
        $dayOfWeek = $date->dayOfWeek; // 0 = Chủ nhật, 1-6 = Thứ 2 - Thứ 7
        
        try {
            // Lấy danh sách tỉnh mở thưởng vào ngày này
            $provinces = Province::where('draw_day', $dayOfWeek)
                ->where('is_active', true)
                ->with('region')
                ->get();
            
            $results = [];
            $errors = [];
            
            // Sử dụng transaction để đảm bảo tất cả các thao tác database được thực hiện hoặc không thực hiện
            DB::beginTransaction();
            
            // Miền Bắc - luôn có kết quả hàng ngày
            try {
                // Lấy kết quả Miền Bắc
                $mbResults = $this->lotteryApiService->getResults($date, 'mb');
                
                if ($mbResults) {
                    // Kiểm tra xem kết quả đã tồn tại chưa
                    $existingResult = LotteryResult::where('draw_date', $date->format('Y-m-d'))
                        ->where('region_id', Region::where('code', 'mb')->first()->id)
                        ->whereNull('province_id')
                        ->first();
                    
                    if ($existingResult) {
                        // Cập nhật kết quả nếu chưa được xử lý
                        if (!$existingResult->is_processed) {
                            $existingResult->results = json_encode($mbResults);
                            $existingResult->save();
                        }
                        
                        // Xử lý các vé cược đang chờ cho kết quả này
                        $processResult = $this->lotteryProcessor->processResult($existingResult);
                        
                        $results['mb'] = [
                            'status' => 'success',
                            'message' => 'Kết quả đã tồn tại',
                            'data' => json_decode($existingResult->results),
                            'is_processed' => $existingResult->is_processed,
                            'processing_result' => $processResult
                        ];
                    } else {
                        // Lưu kết quả mới
                        $lotteryResult = new LotteryResult();
                        $lotteryResult->region_id = Region::where('code', 'mb')->first()->id;
                        $lotteryResult->province_id = null;
                        $lotteryResult->draw_date = $date->format('Y-m-d');
                        $lotteryResult->results = json_encode($mbResults);
                        $lotteryResult->save();
                        
                        // Xử lý các vé cược đang chờ cho kết quả này
                        $processResult = $this->lotteryProcessor->processResult($lotteryResult);
                        
                        $results['mb'] = [
                            'status' => 'success',
                            'message' => 'Đã lấy kết quả xổ số thành công',
                            'data' => $mbResults,
                            'is_processed' => false,
                            'processing_result' => $processResult
                        ];
                    }
                } else {
                    $errors['mb'] = 'Không thể lấy kết quả xổ số';
                }
            } catch (\Exception $e) {
                $errors['mb'] = 'Lỗi: ' . $e->getMessage();
            }
            
            // Miền Nam và Miền Trung - theo lịch mở thưởng
            foreach (['mn', 'mt'] as $region) {
                $regionProvinces = $provinces->where('region.code', $region);
                
                if (!isset($results[$region])) {
                    $results[$region] = [];
                }
                
                if (!isset($errors[$region])) {
                    $errors[$region] = [];
                }
                
                foreach ($regionProvinces as $province) {
                    try {
                        // Lấy kết quả cho tỉnh
                        $provinceResults = $this->lotteryApiService->getResults($date, $region, $province->code);
                        
                        if ($provinceResults) {
                            // Kiểm tra xem kết quả đã tồn tại chưa
                            $existingResult = LotteryResult::where('draw_date', $date->format('Y-m-d'))
                                ->where('region_id', $province->region_id)
                                ->where('province_id', $province->id)
                                ->first();
                            
                            if ($existingResult) {
                                // Cập nhật kết quả nếu chưa được xử lý
                                if (!$existingResult->is_processed) {
                                    $existingResult->results = json_encode($provinceResults);
                                    $existingResult->save();
                                }
                                
                                // Xử lý các vé cược đang chờ cho kết quả này
                                $processResult = $this->lotteryProcessor->processResult($existingResult);
                                
                                $results[$region][$province->code] = [
                                    'status' => 'success',
                                    'message' => 'Kết quả đã tồn tại',
                                    'data' => json_decode($existingResult->results),
                                    'is_processed' => $existingResult->is_processed,
                                    'processing_result' => $processResult
                                ];
                            } else {
                                $lotteryResult = new LotteryResult();
                                $lotteryResult->region_id = $province->region_id;
                                $lotteryResult->province_id = $province->id;
                                $lotteryResult->draw_date = $date->format('Y-m-d');
                                $lotteryResult->results = json_encode($provinceResults);
                                
                                try {
                                    $saveResult = $lotteryResult->save();
                                    Log::info('Save lottery result', [
                                        'region' => $region,
                                        'province' => $province->code,
                                        'date' => $date->format('Y-m-d'),
                                        'save_result' => $saveResult,
                                        'lottery_result_id' => $lotteryResult->id ?? 'no_id'
                                    ]);
                                    
                                    // Chỉ xử lý kết quả nếu lưu thành công
                                    if ($saveResult) {
                                        // Xử lý các vé cược đang chờ cho kết quả này
                                        $processResult = $this->processResultWithoutTransaction($lotteryResult);
                                        
                                        $results[$region][$province->code] = [
                                            'status' => 'success',
                                            'message' => 'Đã lấy kết quả xổ số thành công',
                                            'data' => $provinceResults,
                                            'is_processed' => false,
                                            'processing_result' => $processResult
                                        ];
                                    } else {
                                        Log::error('Failed to save lottery result', [
                                            'region' => $region,
                                            'province' => $province->code,
                                            'date' => $date->format('Y-m-d')
                                        ]);
                                        $errors[$region][$province->code] = 'Không thể lưu kết quả xổ số vào database';
                                    }
                                } catch (\Exception $e) {
                                    Log::error('Error saving lottery result', [
                                        'region' => $region,
                                        'province' => $province->code,
                                        'date' => $date->format('Y-m-d'),
                                        'error' => $e->getMessage(),
                                        'trace' => $e->getTraceAsString()
                                    ]);
                                    
                                    $errors[$region][$province->code] = 'Lỗi: ' . $e->getMessage();
                                }
                            }
                        } else {
                            $errors[$region][$province->code] = 'Không thể lấy kết quả xổ số';
                        }
                    } catch (\Exception $e) {
                        $errors[$region][$province->code] = 'Lỗi: ' . $e->getMessage();
                    }
                }
            }
            
            // Commit transaction nếu không có lỗi
            DB::commit();
            
            return response()->json([
                'status' => 'success',
                'message' => 'Đã lấy kết quả xổ số cho ngày ' . $date->format('d/m/Y'),
                'data' => $results,
                'errors' => $errors
            ]);
        } catch (\Exception $e) {
            // Rollback transaction nếu có lỗi
            DB::rollBack();
            
            Log::error('Error in fetchAllResults: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Đã xảy ra lỗi: ' . $e->getMessage()
            ]);
        }
    }
    
    /**
     * Xử lý kết quả xổ số mà không chuyển hướng
     */
    private function processResultWithoutRedirect(LotteryResult $result)
    {
        try {
            // Gọi processResult nhưng bắt và xử lý bất kỳ redirect nào
            $processResult = $this->lotteryProcessor->processResult($result);
            return $processResult;
        } catch (\Exception $e) {
            Log::error('Error in processResultWithoutRedirect: ' . $e->getMessage(), [
                'result_id' => $result->id,
                'exception' => $e
            ]);
            return [
                'status' => 'error',
                'message' => 'Đã xảy ra lỗi khi xử lý kết quả: ' . $e->getMessage()
            ];
        }
    }
    
    private function processResultWithoutTransaction(LotteryResult $result)
    {
        try {
            // Gọi processResult với tham số useTransaction=false
            $processResult = $this->lotteryProcessor->processResult($result, false);
            return $processResult;
        } catch (\Exception $e) {
            Log::error('Error in processResultWithoutTransaction: ' . $e->getMessage(), [
                'result_id' => $result->id,
                'exception' => $e
            ]);
            return [
                'status' => 'error',
                'message' => 'Đã xảy ra lỗi khi xử lý kết quả: ' . $e->getMessage()
            ];
        }
    }
}