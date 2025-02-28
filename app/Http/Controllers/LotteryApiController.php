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
            'auto_process' => 'nullable|boolean'
        ]);
        
        $date = $request->input('date') ? Carbon::parse($request->input('date')) : Carbon::now();
        $region = $request->input('region');
        $province = $request->input('province');
        $autoProcess = $request->input('auto_process', false);
        
        try {
            // Lấy region_id
            $regionModel = Region::where('code', $region)->first();
            if (!$regionModel) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Không tìm thấy vùng miền ' . $region
                ], 404);
            }
            
            // Lấy province_id nếu có
            $provinceModel = null;
            if ($province) {
                $provinceModel = Province::where('code', $province)
                    ->where('region_id', $regionModel->id)
                    ->first();
                
                if (!$provinceModel) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Không tìm thấy tỉnh ' . $province . ' trong vùng miền ' . $region
                    ], 404);
                }
            }
            
            // Kiểm tra xem kết quả đã tồn tại chưa
            $existingResult = LotteryResult::where('draw_date', $date->format('Y-m-d'))
                ->where('region_id', $regionModel->id);
            
            if ($provinceModel) {
                $existingResult->where('province_id', $provinceModel->id);
            } else {
                $existingResult->whereNull('province_id');
            }
            
            if ($existingResult->exists()) {
                $result = $existingResult->first();
                
                if ($result->is_processed) {
                    return response()->json([
                        'status' => 'warning',
                        'message' => 'Kết quả cho ngày này đã tồn tại và đã được xử lý',
                        'data' => json_decode($result->results)
                    ]);
                }
                
                return response()->json([
                    'status' => 'warning',
                    'message' => 'Kết quả cho ngày này đã tồn tại nhưng chưa được xử lý',
                    'data' => json_decode($result->results)
                ]);
            }
            
            // Lấy kết quả từ API
            $results = $this->lotteryApiService->getResults($date, $region, $province);
            
            if (!$results) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Không thể lấy kết quả xổ số cho ngày ' . $date->format('d/m/Y')
                ], 404);
            }
            
            // Lưu kết quả vào cơ sở dữ liệu
            $lotteryResult = new LotteryResult();
            $lotteryResult->region_id = $regionModel->id;
            $lotteryResult->province_id = $provinceModel ? $provinceModel->id : null;
            $lotteryResult->draw_date = $date->format('Y-m-d');
            $lotteryResult->results = json_encode($results);
            $lotteryResult->is_processed = false;
            $lotteryResult->save();
            
            // Xử lý kết quả nếu yêu cầu
            $processResult = null;
            if ($autoProcess) {
                $processResult = $this->lotteryProcessor->processResult($lotteryResult);
            }
            
            return response()->json([
                'status' => 'success',
                'message' => 'Lấy kết quả xổ số thành công',
                'data' => $results,
                'process_result' => $processResult
            ]);
        } catch (\Exception $e) {
            Log::error('Error in fetchResults: ' . $e->getMessage(), [
                'date' => $date->format('Y-m-d'),
                'region' => $region,
                'province' => $province,
                'exception' => $e
            ]);
            
            return response()->json([
                'status' => 'error',
                'message' => 'Đã xảy ra lỗi: ' . $e->getMessage()
            ], 500);
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
            'province' => 'nullable|string'
        ]);
        
        $date = $request->input('date') ? Carbon::parse($request->input('date')) : Carbon::now();
        $region = $request->input('region');
        $province = $request->input('province');
        
        try {
            // Lấy kết quả từ API
            $results = $this->lotteryApiService->getResults($date, $region, $province);
            
            if (!$results) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Không thể lấy kết quả xổ số cho ngày ' . $date->format('d/m/Y')
                ], 404);
            }
            
            return response()->json([
                'status' => 'success',
                'message' => 'Lấy kết quả xổ số thành công',
                'data' => $results
            ]);
        } catch (\Exception $e) {
            Log::error('Error in getResults: ' . $e->getMessage(), [
                'date' => $date->format('Y-m-d'),
                'region' => $region,
                'province' => $province,
                'exception' => $e
            ]);
            
            return response()->json([
                'status' => 'error',
                'message' => 'Đã xảy ra lỗi: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Lấy kết quả và cập nhật tự động cho tất cả tỉnh trong ngày
     */
    public function fetchAllResults(Request $request)
    {
        $request->validate([
            'date' => 'nullable|date',
            'auto_process' => 'nullable|boolean'
        ]);
        
        $date = $request->input('date') ? Carbon::parse($request->input('date')) : Carbon::now();
        $autoProcess = $request->input('auto_process', false);
        $dayOfWeek = $date->dayOfWeek; // 0 = Chủ nhật, 1-6 = Thứ 2 - Thứ 7
        
        try {
            // Lấy danh sách tỉnh mở thưởng vào ngày này
            $provinces = Province::where('draw_day', $dayOfWeek)
                ->where('is_active', true)
                ->get();
            
            $results = [];
            $errors = [];
            
            // Xử lý Miền Bắc (chỉ có 1 kết quả)
            $mbProvinces = $provinces->where('region.code', 'mb');
            if ($mbProvinces->isNotEmpty()) {
                try {
                    $mbResults = $this->lotteryApiService->getResults($date, 'mb');
                    
                    if ($mbResults) {
                        // Lưu kết quả Miền Bắc
                        $regionModel = Region::where('code', 'mb')->first();
                        
                        // Kiểm tra xem kết quả đã tồn tại chưa
                        $existingResult = LotteryResult::where('draw_date', $date->format('Y-m-d'))
                            ->where('region_id', $regionModel->id)
                            ->whereNull('province_id')
                            ->first();
                        
                        if ($existingResult) {
                            if (!$existingResult->is_processed && $autoProcess) {
                                $processResult = $this->lotteryProcessor->processResult($existingResult);
                                $results['mb'] = [
                                    'status' => 'success',
                                    'message' => 'Kết quả đã tồn tại và đã được xử lý',
                                    'process_result' => $processResult
                                ];
                            } else {
                                $results['mb'] = [
                                    'status' => 'success',
                                    'message' => 'Kết quả đã tồn tại' . ($existingResult->is_processed ? ' và đã được xử lý' : ' nhưng chưa được xử lý')
                                ];
                            }
                        } else {
                            // Lưu kết quả mới
                            $lotteryResult = new LotteryResult();
                            $lotteryResult->region_id = $regionModel->id;
                            $lotteryResult->province_id = null;
                            $lotteryResult->draw_date = $date->format('Y-m-d');
                            $lotteryResult->results = json_encode($mbResults);
                            $lotteryResult->is_processed = false;
                            $lotteryResult->save();
                            
                            // Xử lý kết quả nếu yêu cầu
                            $processResult = null;
                            if ($autoProcess) {
                                $processResult = $this->lotteryProcessor->processResult($lotteryResult);
                            }
                            
                            $results['mb'] = [
                                'status' => 'success',
                                'message' => 'Lấy và lưu kết quả thành công',
                                'process_result' => $processResult
                            ];
                        }
                    } else {
                        $errors['mb'] = 'Không thể lấy kết quả xổ số miền Bắc';
                    }
                } catch (\Exception $e) {
                    $errors['mb'] = 'Lỗi: ' . $e->getMessage();
                }
            }
            
            // Xử lý Miền Nam và Miền Trung (nhiều tỉnh)
            foreach (['mn', 'mt'] as $region) {
                $regionProvinces = $provinces->where('region.code', $region);
                
                foreach ($regionProvinces as $province) {
                    try {
                        $provinceResults = $this->lotteryApiService->getResults($date, $region, $province->code);
                        
                        if ($provinceResults) {
                            // Kiểm tra xem kết quả đã tồn tại chưa
                            $existingResult = LotteryResult::where('draw_date', $date->format('Y-m-d'))
                                ->where('region_id', $province->region_id)
                                ->where('province_id', $province->id)
                                ->first();
                            
                            if ($existingResult) {
                                if (!$existingResult->is_processed && $autoProcess) {
                                    $processResult = $this->lotteryProcessor->processResult($existingResult);
                                    $results[$region][$province->code] = [
                                        'status' => 'success',
                                        'message' => 'Kết quả đã tồn tại và đã được xử lý',
                                        'process_result' => $processResult
                                    ];
                                } else {
                                    $results[$region][$province->code] = [
                                        'status' => 'success',
                                        'message' => 'Kết quả đã tồn tại' . ($existingResult->is_processed ? ' và đã được xử lý' : ' nhưng chưa được xử lý')
                                    ];
                                }
                            } else {
                                // Lưu kết quả mới
                                $lotteryResult = new LotteryResult();
                                $lotteryResult->region_id = $province->region_id;
                                $lotteryResult->province_id = $province->id;
                                $lotteryResult->draw_date = $date->format('Y-m-d');
                                $lotteryResult->results = json_encode($provinceResults);
                                $lotteryResult->is_processed = false;
                                $lotteryResult->save();
                                
                                // Xử lý kết quả nếu yêu cầu
                                $processResult = null;
                                if ($autoProcess) {
                                    $processResult = $this->lotteryProcessor->processResult($lotteryResult);
                                }
                                
                                $results[$region][$province->code] = [
                                    'status' => 'success',
                                    'message' => 'Lấy và lưu kết quả thành công',
                                    'process_result' => $processResult
                                ];
                            }
                        } else {
                            $errors[$region][$province->code] = 'Không thể lấy kết quả xổ số';
                        }
                    } catch (\Exception $e) {
                        $errors[$region][$province->code] = 'Lỗi: ' . $e->getMessage();
                    }
                }
            }
            
            return response()->json([
                'status' => count($errors) > 0 ? 'partial' : 'success',
                'date' => $date->format('Y-m-d'),
                'results' => $results,
                'errors' => $errors
            ]);
        } catch (\Exception $e) {
            Log::error('Error in fetchAllResults: ' . $e->getMessage(), [
                'date' => $date->format('Y-m-d'),
                'exception' => $e
            ]);
            
            return response()->json([
                'status' => 'error',
                'message' => 'Đã xảy ra lỗi: ' . $e->getMessage()
            ], 500);
        }
    }
}