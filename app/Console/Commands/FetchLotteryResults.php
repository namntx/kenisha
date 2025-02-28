<?php

namespace App\Console\Commands;

use App\Services\LotteryApiService;
use App\Services\LotteryProcessorService;
use App\Models\Province;
use App\Models\Region;
use App\Models\LotteryResult;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class FetchLotteryResults extends Command
{
    protected $signature = 'lottery:fetch {--date= : Ngày cần lấy kết quả (format: Y-m-d)} {--process : Tự động xử lý kết quả}';
    protected $description = 'Lấy kết quả xổ số từ các nguồn internet và lưu vào cơ sở dữ liệu';

    protected $lotteryApiService;
    protected $lotteryProcessor;

    public function __construct(LotteryApiService $lotteryApiService, LotteryProcessorService $lotteryProcessor)
    {
        parent::__construct();
        $this->lotteryApiService = $lotteryApiService;
        $this->lotteryProcessor = $lotteryProcessor;
    }

    public function handle()
    {
        $date = $this->option('date') ? Carbon::parse($this->option('date')) : Carbon::now();
        $autoProcess = $this->option('process');
        $dayOfWeek = $date->dayOfWeek; // 0 = Chủ nhật, 1-6 = Thứ 2 - Thứ 7
        
        $this->info('Fetching lottery results for ' . $date->format('Y-m-d'));
        
        try {
            // Lấy danh sách tỉnh mở thưởng vào ngày này
            $provinces = Province::where('draw_day', $dayOfWeek)
                ->where('is_active', true)
                ->get();
            
            $successCount = 0;
            $errorCount = 0;
            
            // Xử lý Miền Bắc (chỉ có 1 kết quả)
            $mbProvinces = $provinces->where('region.code', 'mb');
            if ($mbProvinces->isNotEmpty()) {
                try {
                    $this->info('Fetching results for Miền Bắc...');
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
                                $this->info('Processing existing results for Miền Bắc...');
                                $processResult = $this->lotteryProcessor->processResult($existingResult);
                                $this->info('Processed results for Miền Bắc: ' . json_encode($processResult));
                            } else {
                                $this->info('Results for Miền Bắc already exist' . ($existingResult->is_processed ? ' and have been processed' : ' but have not been processed'));
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
                            
                            $this->info('Saved results for Miền Bắc');
                            
                            // Xử lý kết quả nếu yêu cầu
                            if ($autoProcess) {
                                $this->info('Processing results for Miền Bắc...');
                                $processResult = $this->lotteryProcessor->processResult($lotteryResult);
                                $this->info('Processed results for Miền Bắc: ' . json_encode($processResult));
                            }
                        }
                        
                        $successCount++;
                    } else {
                        $this->error('Could not fetch results for Miền Bắc');
                        $errorCount++;
                    }
                } catch (\Exception $e) {
                    $this->error('Error processing Miền Bắc: ' . $e->getMessage());
                    $errorCount++;
                }
            }
            
            // Xử lý Miền Nam và Miền Trung (nhiều tỉnh)
            foreach (['mn', 'mt'] as $region) {
                $regionProvinces = $provinces->where('region.code', $region);
                
                foreach ($regionProvinces as $province) {
                    try {
                        $this->info("Fetching results for {$province->name} ({$region})...");
                        $provinceResults = $this->lotteryApiService->getResults($date, $region, $province->code);
                        
                        if ($provinceResults) {
                            // Kiểm tra xem kết quả đã tồn tại chưa
                            $existingResult = LotteryResult::where('draw_date', $date->format('Y-m-d'))
                                ->where('region_id', $province->region_id)
                                ->where('province_id', $province->id)
                                ->first();
                            
                            if ($existingResult) {
                                if (!$existingResult->is_processed && $autoProcess) {
                                    $this->info("Processing existing results for {$province->name}...");
                                    $processResult = $this->lotteryProcessor->processResult($existingResult);
                                    $this->info("Processed results for {$province->name}: " . json_encode($processResult));
                                } else {
                                    $this->info("Results for {$province->name} already exist" . ($existingResult->is_processed ? ' and have been processed' : ' but have not been processed'));
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
                                
                                $this->info("Saved results for {$province->name}");
                                
                                // Xử lý kết quả nếu yêu cầu
                                if ($autoProcess) {
                                    $this->info("Processing results for {$province->name}...");
                                    $processResult = $this->lotteryProcessor->processResult($lotteryResult);
                                    $this->info("Processed results for {$province->name}: " . json_encode($processResult));
                                }
                            }
                            
                            $successCount++;
                        } else {
                            $this->error("Could not fetch results for {$province->name}");
                            $errorCount++;
                        }
                    } catch (\Exception $e) {
                        $this->error("Error processing {$province->name}: " . $e->getMessage());
                        $errorCount++;
                    }
                }
            }
            
            $this->info("Command completed with {$successCount} successes and {$errorCount} errors");
            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error('Error: ' . $e->getMessage());
            Log::error('Error in FetchLotteryResults command: ' . $e->getMessage(), [
                'date' => $date->format('Y-m-d'),
                'exception' => $e
            ]);
            
            return Command::FAILURE;
        }
    }
}