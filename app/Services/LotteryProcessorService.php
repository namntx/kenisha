<?php

namespace App\Services;

use App\Models\Bet;
use App\Models\LotteryResult;
use App\Models\User;
use App\Models\CustomerSetting;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class LotteryProcessorService
{
    /**
     * Xử lý tất cả các vé cược cho một kết quả xổ số
     */
    public function processResult(LotteryResult $result, bool $useTransaction = true)
    {
        if ($useTransaction) {
            DB::beginTransaction();
        }
        
        try {
            // Lấy tất cả vé cược chưa xử lý cho ngày và tỉnh/khu vực này
            $bets = $this->getBetsForResult($result);
            
            if ($bets->isEmpty()) {
                if ($useTransaction) {
                    DB::commit();
                }
                return ['status' => 'success', 'message' => 'Không có vé cược nào cần xử lý'];
            }
            
            // Xử lý từng vé cược
            foreach ($bets as $bet) {
                $this->processBet($bet, $result);
            }
            
            // Đánh dấu kết quả đã xử lý
            $result->is_processed = true;
            $result->save();
            
            if ($useTransaction) {
                DB::commit();
            }
            
            return [
                'status' => 'success', 
                'message' => 'Đã xử lý ' . $bets->count() . ' vé cược',
                'processed_count' => $bets->count()
            ];
        } catch (\Exception $e) {
            if ($useTransaction) {
                DB::rollBack();
            }
            Log::error('Lỗi xử lý kết quả xổ số: ' . $e->getMessage(), [
                'result_id' => $result->id,
                'exception' => $e
            ]);
            
            return [
                'status' => 'error', 
                'message' => 'Lỗi xử lý kết quả: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Lấy các vé cược cho kết quả xổ số này
     */
    private function getBetsForResult(LotteryResult $result)
    {
        $query = Bet::where('bet_date', $result->draw_date)
                  ->where('is_processed', false);
        
        // Nếu có province_id, lọc theo province_id
        if ($result->province_id) {
            $query->where('province_id', $result->province_id);
        } else {
            // Nếu không có province_id, lọc theo region_id
            $query->where('region_id', $result->region_id)
                  ->whereNull('province_id');
        }
        
        return $query->get();
    }
    
    /**
     * Xử lý một vé cược
     */
    public function processBet(Bet $bet, LotteryResult $result)
    {
        // Lấy kết quả xổ số
        $resultData = json_decode($result->results, true);
        
        // Lấy loại cược
        $betType = $bet->betType->code;
        
        // Lấy cài đặt của khách hàng
        $customer = $bet->user;
        $setting = $customer->setting;
        
        // Xác định vùng miền để áp dụng cài đặt tương ứng
        $region = $bet->region->code;
        
        // Kiểm tra có trúng không và số tiền thắng
        list($isWin, $winAmount) = $this->checkWin($bet, $resultData, $betType, $region, $setting);
        
        // Cập nhật thông tin vé
        $bet->is_processed = true;
        $bet->is_won = $isWin;
        $bet->win_amount = $isWin ? $winAmount : 0;
        $bet->save();
    }
    
    /**
     * Kiểm tra vé có trúng không và tính số tiền thắng
     *
     * @return array [boolean $isWin, float $winAmount]
     */
    private function checkWin(Bet $bet, array $resultData, string $betType, string $region, ?CustomerSetting $setting)
    {
        $numbers = $bet->numbers;
        $amount = $bet->amount;
        
        // Nếu không có cài đặt, sử dụng giá trị mặc định
        if (!$setting) {
            // Giá trị thắng mặc định
            $winMultiplier = 1;
            return [false, 0]; // Mặc định không trúng
        }
        
        // Xử lý dựa vào loại cược và vùng miền
        switch ($region) {
            case 'mn': // Miền Nam
                return $this->checkWinSouth($numbers, $resultData, $betType, $amount, $setting);
                
            case 'mb': // Miền Bắc
                return $this->checkWinNorth($numbers, $resultData, $betType, $amount, $setting);
                
            case 'mt': // Miền Trung
                return $this->checkWinCentral($numbers, $resultData, $betType, $amount, $setting);
                
            default:
                return [false, 0];
        }
    }
    
    /**
     * Kiểm tra trúng thưởng Miền Nam
     */
    private function checkWinSouth($numbers, $resultData, $betType, $amount, $setting)
    {
        // Lấy kết quả giải
        $specialPrize = $resultData['special'] ?? '';
        $firstPrize = $resultData['first'] ?? '';
        $allPrizes = $this->getAllPrizesFromResult($resultData);
        
        switch ($betType) {
            case 'de': // Đề - 2 số cuối giải đặc biệt
                $last2Digits = substr($specialPrize, -2);
                $isWin = $numbers === $last2Digits;
                $winAmount = $isWin ? $amount * $setting->south_head_tail_win : 0;
                return [$isWin, $winAmount];
                
            case 'lo': // Lô - 2 số cuối xuất hiện ở bất kỳ giải nào
                $count = $this->countOccurrences($numbers, $allPrizes, 2);
                $isWin = $count > 0;
                $winAmount = $count * $amount * $setting->south_lo_win;
                return [$isWin, $winAmount];
                
            case '3c': // 3 Càng - 3 số cuối giải đặc biệt
                $last3Digits = substr($specialPrize, -3);
                $isWin = $numbers === $last3Digits;
                $winAmount = $isWin ? $amount * $setting->south_3_digits_win : 0;
                return [$isWin, $winAmount];
                
            case '4c': // 4 Càng - 4 số cuối giải đặc biệt
                $last4Digits = substr($specialPrize, -4);
                $isWin = $numbers === $last4Digits;
                $winAmount = $isWin ? $amount * $setting->south_4_digits_win : 0;
                return [$isWin, $winAmount];
                
            case 'dau': // Đầu - chữ số đầu tiên giải đặc biệt
                $firstDigit = substr($specialPrize, 0, 1);
                $isWin = $numbers === $firstDigit;
                $winAmount = $isWin ? $amount * $setting->south_head_tail_win : 0;
                return [$isWin, $winAmount];
                
            case 'duoi': // Đuôi - chữ số cuối cùng giải đặc biệt
                $lastDigit = substr($specialPrize, -1);
                $isWin = $numbers === $lastDigit;
                $winAmount = $isWin ? $amount * $setting->south_head_tail_win : 0;
                return [$isWin, $winAmount];
                
            case 'da_thang': // Đá thẳng
                $winType = $setting->south_straight_win_type ?? 2;
                $firstLast2 = substr($firstPrize, -2);
                $specialLast2 = substr($specialPrize, -2);
                $pair = [$firstLast2, $specialLast2];
                $isWin = $this->checkDaThang($numbers, $pair, $winType);
                $winAmount = $isWin ? $amount * $setting->south_straight_win : 0;
                return [$isWin, $winAmount];
                
            case 'da_xien': // Đá xiên
                $winType = $setting->south_slide_win_type ?? 3;
                $isWin = $this->checkDaXien($numbers, $allPrizes, $winType);
                $winAmount = $isWin ? $amount * $setting->south_slide_win : 0;
                return [$isWin, $winAmount];
                
            default:
                return [false, 0];
        }
    }
    
    /**
     * Kiểm tra trúng thưởng Miền Bắc
     */
    private function checkWinNorth($numbers, $resultData, $betType, $amount, $setting)
    {
        // Lấy kết quả giải
        $specialPrize = $resultData['special'] ?? '';
        $allPrizes = $this->getAllPrizesFromResult($resultData);
        
        switch ($betType) {
            case 'de': // Đề - 2 số cuối giải đặc biệt
                $last2Digits = substr($specialPrize, -2);
                $isWin = $numbers === $last2Digits;
                $winAmount = $isWin ? $amount * $setting->north_head_tail_win : 0;
                return [$isWin, $winAmount];
                
            case 'lo': // Lô - 2 số cuối xuất hiện ở bất kỳ giải nào
                $count = $this->countOccurrences($numbers, $allPrizes, 2);
                $isWin = $count > 0;
                $winAmount = $count * $amount * $setting->north_lo_win;
                return [$isWin, $winAmount];
                
            case '3c': // 3 Càng - 3 số cuối giải đặc biệt
                $last3Digits = substr($specialPrize, -3);
                $isWin = $numbers === $last3Digits;
                $winAmount = $isWin ? $amount * $setting->north_3_digits_win : 0;
                return [$isWin, $winAmount];
                
            case '4c': // 4 Càng - 4 số cuối giải đặc biệt
                $last4Digits = substr($specialPrize, -4);
                $isWin = $numbers === $last4Digits;
                $winAmount = $isWin ? $amount * $setting->north_4_digits_win : 0;
                return [$isWin, $winAmount];
                
            case 'dau': // Đầu - chữ số đầu tiên giải đặc biệt
                $firstDigit = substr($specialPrize, 0, 1);
                $isWin = $numbers === $firstDigit;
                $winAmount = $isWin ? $amount * $setting->north_head_tail_win : 0;
                return [$isWin, $winAmount];
                
            case 'duoi': // Đuôi - chữ số cuối cùng giải đặc biệt
                $lastDigit = substr($specialPrize, -1);
                $isWin = $numbers === $lastDigit;
                $winAmount = $isWin ? $amount * $setting->north_head_tail_win : 0;
                return [$isWin, $winAmount];
                
            case 'da': // Đá Miền Bắc
                $winType = $setting->north_slide_win_type ?? 2;
                $isWin = $this->checkDa($numbers, $allPrizes, $winType);
                $winAmount = $isWin ? $amount * $setting->north_slide_win : 0;
                return [$isWin, $winAmount];
                
            case 'xien2': // Xiên 2
                $isWin = $this->checkXien($numbers, $allPrizes, 2);
                $winAmount = $isWin ? $amount * $setting->north_slide2_win : 0;
                return [$isWin, $winAmount];
                
            case 'xien3': // Xiên 3
                $isWin = $this->checkXien($numbers, $allPrizes, 3);
                $winAmount = $isWin ? $amount * $setting->north_slide3_win : 0;
                return [$isWin, $winAmount];
                
            case 'xien4': // Xiên 4
                $isWin = $this->checkXien($numbers, $allPrizes, 4);
                $winAmount = $isWin ? $amount * $setting->north_slide4_win : 0;
                return [$isWin, $winAmount];
                
            case 'xien5': // Xiên 5
                $isWin = $this->checkXien($numbers, $allPrizes, 5);
                $winAmount = $isWin ? $amount * $setting->north_slide5_win : 0;
                return [$isWin, $winAmount];
                
            case 'xien6': // Xiên 6
                $isWin = $this->checkXien($numbers, $allPrizes, 6);
                $winAmount = $isWin ? $amount * $setting->north_slide6_win : 0;
                return [$isWin, $winAmount];
                
            default:
                return [false, 0];
        }
    }
    
    /**
     * Kiểm tra trúng thưởng Miền Trung
     */
    private function checkWinCentral($numbers, $resultData, $betType, $amount, $setting)
    {
        // Lấy kết quả giải
        $specialPrize = $resultData['special'] ?? '';
        $firstPrize = $resultData['first'] ?? '';
        $allPrizes = $this->getAllPrizesFromResult($resultData);
        
        switch ($betType) {
            case 'de': // Đề - 2 số cuối giải đặc biệt
                $last2Digits = substr($specialPrize, -2);
                $isWin = $numbers === $last2Digits;
                $winAmount = $isWin ? $amount * $setting->central_head_tail_win : 0;
                return [$isWin, $winAmount];
                
            case 'lo': // Lô - 2 số cuối xuất hiện ở bất kỳ giải nào
                $count = $this->countOccurrences($numbers, $allPrizes, 2);
                $isWin = $count > 0;
                $winAmount = $count * $amount * $setting->central_lo_win;
                return [$isWin, $winAmount];
                
            case '3c': // 3 Càng - 3 số cuối giải đặc biệt
                $last3Digits = substr($specialPrize, -3);
                $isWin = $numbers === $last3Digits;
                $winAmount = $isWin ? $amount * $setting->central_3_digits_win : 0;
                return [$isWin, $winAmount];
                
            case '4c': // 4 Càng - 4 số cuối giải đặc biệt
                $last4Digits = substr($specialPrize, -4);
                $isWin = $numbers === $last4Digits;
                $winAmount = $isWin ? $amount * $setting->central_4_digits_win : 0;
                return [$isWin, $winAmount];
                
            case 'dau': // Đầu - chữ số đầu tiên giải đặc biệt
                $firstDigit = substr($specialPrize, 0, 1);
                $isWin = $numbers === $firstDigit;
                $winAmount = $isWin ? $amount * $setting->central_head_tail_win : 0;
                return [$isWin, $winAmount];
                
            case 'duoi': // Đuôi - chữ số cuối cùng giải đặc biệt
                $lastDigit = substr($specialPrize, -1);
                $isWin = $numbers === $lastDigit;
                $winAmount = $isWin ? $amount * $setting->central_head_tail_win : 0;
                return [$isWin, $winAmount];
                
            case 'da_thang': // Đá thẳng
                $winType = $setting->central_straight_win_type ?? 2;
                $firstLast2 = substr($firstPrize, -2);
                $specialLast2 = substr($specialPrize, -2);
                $pair = [$firstLast2, $specialLast2];
                $isWin = $this->checkDaThang($numbers, $pair, $winType);
                $winAmount = $isWin ? $amount * $setting->central_straight_win : 0;
                return [$isWin, $winAmount];
                
            case 'da_xien': // Đá xiên
                $winType = $setting->central_slide_win_type ?? 3;
                $isWin = $this->checkDaXien($numbers, $allPrizes, $winType);
                $winAmount = $isWin ? $amount * $setting->central_slide_win : 0;
                return [$isWin, $winAmount];
                
            default:
                return [false, 0];
        }
    }
    
    /**
     * Lấy tất cả các giải từ kết quả
     */
    private function getAllPrizesFromResult($resultData)
    {
        $allPrizes = [];
        
        foreach ($resultData as $key => $value) {
            if (is_array($value)) {
                foreach ($value as $prize) {
                    $allPrizes[] = $prize;
                }
            } else {
                $allPrizes[] = $value;
            }
        }
        
        return $allPrizes;
    }
    
    /**
     * Đếm số lần xuất hiện của dãy số trong các giải
     */
    private function countOccurrences($numbers, $allPrizes, $lastDigits = 2)
    {
        $count = 0;
        
        foreach ($allPrizes as $prize) {
            if (strlen($prize) >= $lastDigits) {
                $prizeLastDigits = substr($prize, -$lastDigits);
                if ($prizeLastDigits === $numbers) {
                    $count++;
                }
            }
        }
        
        return $count;
    }
    
    /**
     * Kiểm tra Đá Thẳng
     */
    private function checkDaThang($numbers, $pair, $winType)
    {
        // Tách cặp số đánh thành 2 số riêng biệt (mỗi số 2 chữ số)
        if (strlen($numbers) == 4) {
            $num1 = substr($numbers, 0, 2);
            $num2 = substr($numbers, 2, 2);
        } else {
            return false; // Không đúng định dạng
        }

        // Một lần: Số đã đánh phải trùng chính xác với cặp số trúng
        if ($winType == 1) {
            return ($num1 == $pair[0] && $num2 == $pair[1]) || 
                ($num1 == $pair[1] && $num2 == $pair[0]);
        }
        
        // Ky rưỡi: Cho phép đánh cả số đảo
        else if ($winType == 2) {
            $reversed1 = strrev($num1);
            $reversed2 = strrev($num2);
            
            return ($num1 == $pair[0] && $num2 == $pair[1]) || 
                ($num1 == $pair[1] && $num2 == $pair[0]) ||
                ($reversed1 == $pair[0] && $num2 == $pair[1]) || 
                ($num1 == $pair[0] && $reversed2 == $pair[1]) ||
                ($reversed1 == $pair[1] && $num2 == $pair[0]) || 
                ($num1 == $pair[1] && $reversed2 == $pair[0]);
        }
        
        // Nhiều cặp: Cho phép đánh cả số đảo và các tổ hợp khác
        else if ($winType == 3) {
            $reversed1 = strrev($num1);
            $reversed2 = strrev($num2);
            
            // Kiểm tra tất cả các tổ hợp có thể
            return ($num1 == $pair[0] && $num2 == $pair[1]) || 
                ($num1 == $pair[1] && $num2 == $pair[0]) ||
                ($reversed1 == $pair[0] && $num2 == $pair[1]) || 
                ($num1 == $pair[0] && $reversed2 == $pair[1]) ||
                ($reversed1 == $pair[1] && $num2 == $pair[0]) || 
                ($num1 == $pair[1] && $reversed2 == $pair[0]) ||
                ($reversed1 == $pair[0] && $reversed2 == $pair[1]) || 
                ($reversed1 == $pair[1] && $reversed2 == $pair[0]);
        }
        
        return false;
    }
    
    /**
     * Kiểm tra Đá Xiên
     */
    private function checkDaXien($numbers, $allPrizes, $winType)
    {
        // Tách cặp số đánh
        $num1 = substr($numbers, 0, 2);
        $num2 = substr($numbers, 2, 2);
        
        // Lấy tất cả 2 số cuối của các giải
        $last2DigitsPrizes = [];
        foreach ($allPrizes as $prize) {
            if (strlen($prize) >= 2) {
                $last2DigitsPrizes[] = substr($prize, -2);
            }
        }
        
        // Lọc các số trùng
        $last2DigitsPrizes = array_unique($last2DigitsPrizes);
        
        switch ($winType) {
            case 1: // Một lần
                return in_array($num1, $last2DigitsPrizes) && in_array($num2, $last2DigitsPrizes);
                
            case 2: // Ky rưỡi
                $reversed1 = strrev($num1);
                $reversed2 = strrev($num2);
                
                return (in_array($num1, $last2DigitsPrizes) && in_array($reversed2, $last2DigitsPrizes)) ||
                       (in_array($reversed1, $last2DigitsPrizes) && in_array($num2, $last2DigitsPrizes));
                
            case 3: // Nhiều cặp
                $reversed1 = strrev($num1);
                $reversed2 = strrev($num2);
                
                return (in_array($num1, $last2DigitsPrizes) && in_array($num2, $last2DigitsPrizes)) ||
                       (in_array($reversed1, $last2DigitsPrizes) && in_array($reversed2, $last2DigitsPrizes)) ||
                       (in_array($num1, $last2DigitsPrizes) && in_array($reversed2, $last2DigitsPrizes)) ||
                       (in_array($reversed1, $last2DigitsPrizes) && in_array($num2, $last2DigitsPrizes));
                
            default:
                return false;
        }
    }
    
    /**
     * Kiểm tra Đá (Miền Bắc)
     */
    private function checkDa($numbers, $allPrizes, $winType)
    {
        // Tách cặp số đánh
        $num1 = substr($numbers, 0, 2);
        $num2 = substr($numbers, 2, 2);
        
        // Lấy tất cả 2 số cuối của các giải
        $last2DigitsPrizes = [];
        foreach ($allPrizes as $prize) {
            if (strlen($prize) >= 2) {
                $last2DigitsPrizes[] = substr($prize, -2);
            }
        }
        
        // Lọc các số trùng
        $last2DigitsPrizes = array_unique($last2DigitsPrizes);
        
        switch ($winType) {
            case 1: // Một lần
                return in_array($num1, $last2DigitsPrizes) && in_array($num2, $last2DigitsPrizes);
                
            case 2: // Ky rưỡi
                $reversed1 = strrev($num1);
                $reversed2 = strrev($num2);
                
                return (in_array($num1, $last2DigitsPrizes) && in_array($num2, $last2DigitsPrizes)) ||
                       (in_array($reversed1, $last2DigitsPrizes) && in_array($reversed2, $last2DigitsPrizes));
                
            case 3: // Nhiều cặp
                $reversed1 = strrev($num1);
                $reversed2 = strrev($num2);
                
                return (in_array($num1, $last2DigitsPrizes) && in_array($num2, $last2DigitsPrizes)) ||
                       (in_array($reversed1, $last2DigitsPrizes) && in_array($reversed2, $last2DigitsPrizes)) ||
                       (in_array($num1, $last2DigitsPrizes) && in_array($reversed2, $last2DigitsPrizes)) ||
                       (in_array($reversed1, $last2DigitsPrizes) && in_array($num2, $last2DigitsPrizes));
                
            default:
                return false;
        }
    }
    
    /**
     * Kiểm tra Xiên (Miền Bắc)
     */
    private function checkXien($numbers, $allPrizes, $count)
    {
        // Tách các số
        $numberArray = [];
        for ($i = 0; $i < strlen($numbers); $i += 2) {
            if ($i + 2 <= strlen($numbers)) {
                $numberArray[] = substr($numbers, $i, 2);
            }
        }
        
        // Kiểm tra số lượng số
        if (count($numberArray) !== $count) {
            return false;
        }
        
        // Lấy tất cả 2 số cuối của các giải
        $last2DigitsPrizes = [];
        foreach ($allPrizes as $prize) {
            if (strlen($prize) >= 2) {
                $last2DigitsPrizes[] = substr($prize, -2);
            }
        }
        
        // Lọc các số trùng
        $last2DigitsPrizes = array_unique($last2DigitsPrizes);
        
        // Đếm số lượng số trúng
        $matchCount = 0;
        foreach ($numberArray as $num) {
            if (in_array($num, $last2DigitsPrizes)) {
                $matchCount++;
            }
        }
        
        // Trả về true nếu tất cả các số đều trúng
        return $matchCount === $count;
    }
}