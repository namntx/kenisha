<?php

namespace App\Services;

use App\Models\BetType;
use App\Models\Province;
use App\Models\Region;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;


class BetParserService
{
    public function parse($input, $betDate = null, User $customer = null)
    {
        $input = trim($input);
        $betDate = $betDate ?: Carbon::now();
        $dayOfWeek = $betDate->dayOfWeek; // 0: CN, 1-6: Thứ 2 - Thứ 7
        
        // Mẫu regex cho các thành phần
        $patterns = [
            'numbers' => '/\b\d+\b/',
            'type' => '/\b(de|lo|3c|dau|duoi|dt|da|xien\d?|x\d?)\b/i',
            'region' => '/\b(mb|mt|mn)\b/i',
            'province' => '/\b(mb|tth|py|dlk|qna|dng|kh|bdh|qt|qb|gl|nt|qng|dno|kt|dt|hcm|cm|bt|vt|bl|dna|ct|st|tn|ag|bth|vl|bd|tv|la|hg|bp|tg|kg|dl)\b/i',
            'amount' => '/\b\d+k\b/i',
        ];
        
        $result = [
            'numbers' => [],
            'type' => null,
            'region' => null,
            'province' => null,
            'amount' => null,
            'raw_input' => $input,
            'is_valid' => false,
            'error' => null,
            'bet_date' => $betDate->format('Y-m-d')
        ];
        
        // Trích xuất số đánh
        preg_match_all($patterns['numbers'], $input, $matches);
        $result['numbers'] = $matches[0] ?? [];
        
        // Trích xuất kiểu đánh
        preg_match($patterns['type'], $input, $matches);
        $result['type'] = $matches[0] ?? null;
        
        // Trích xuất tỉnh đài
        preg_match($patterns['province'], $input, $matches);
        $result['province'] = $matches[0] ?? null;
        
        // Nếu không tìm thấy tỉnh, thử tìm khu vực
        if (empty($result['province'])) {
            preg_match($patterns['region'], $input, $matches);
            $result['region'] = $matches[0] ?? null;
        } else {
            // Nếu tìm thấy tỉnh, xác định khu vực từ tỉnh
            $province = Province::where('code', strtolower($result['province']))->first();
            if ($province) {
                $result['region'] = Region::find($province->region_id)->code;
            }
        }
        
        // Trích xuất số tiền
        preg_match($patterns['amount'], $input, $matches);
        if (!empty($matches[0])) {
            $amount = str_replace('k', '000', strtolower($matches[0]));
            $result['amount'] = intval($amount);
        }
        
        // Kiểm tra tính hợp lệ
        if (empty($result['numbers'])) {
            $result['error'] = 'Không tìm thấy số đánh';
        } elseif (empty($result['type'])) {
            $result['error'] = 'Không xác định được kiểu đánh';
        } elseif (empty($result['province']) && empty($result['region'])) {
            $result['error'] = 'Không xác định được tỉnh hoặc khu vực';
        } elseif (empty($result['amount'])) {
            $result['error'] = 'Không xác định được số tiền';
        } else {
            // Kiểm tra xem tỉnh có mở thưởng vào ngày đặt cược không
            if (!empty($result['province'])) {
                $provinceModel = Province::where('code', strtolower($result['province']))
                    ->where('draw_day', $dayOfWeek)
                    ->where('is_active', true)
                    ->first();
                
                if (!$provinceModel) {
                    $result['error'] = "Tỉnh {$result['province']} không mở thưởng vào " . $this->getVietnameseDayOfWeek($dayOfWeek);
                    return $result;
                }
                
                $result['province_id'] = $provinceModel->id;
                $result['region_id'] = $provinceModel->region_id;
                $region = $provinceModel->region->code;
            } else {
                $regionModel = Region::where('code', strtolower($result['region']))->first();
                if ($regionModel) {
                    $result['region_id'] = $regionModel->id;
                    $region = $regionModel->code;
                } else {
                    $result['error'] = "Không tìm thấy khu vực {$result['region']}";
                    return $result;
                }
            }
            
            // Chuẩn hóa kiểu đánh
            $betType = strtolower($result['type']);
            
            // Chuyển đổi các mã kiểu đánh thành mã tiêu chuẩn
            if (in_array($betType, ['de', 'dt', 'dau', 'duoi'])) {
                $standardBetType = 'de'; // Đề, đầu, đuôi
            } elseif ($betType == 'lo') {
                $standardBetType = 'lo'; // Lô
            } elseif ($betType == '3c') {
                $standardBetType = '3c'; // 3 càng
            } elseif (in_array($betType, ['da', 'xien', 'x'])) {
                if ($region == 'mb') {
                    $standardBetType = 'da'; // Đá (Miền Bắc)
                } else {
                    // Phân biệt đá xiên và đá thẳng
                    if (stripos($input, 'thang') !== false || stripos($input, 'th') !== false) {
                        $standardBetType = 'da_thang'; // Đá thẳng (Miền Nam/Trung)
                    } else {
                        $standardBetType = 'da_xien'; // Đá xiên (Miền Nam/Trung)
                    }
                }
            } elseif (preg_match('/xien(\d)/i', $betType, $xienMatches) || preg_match('/x(\d)/i', $betType, $xienMatches)) {
                // Xử lý xiên 2, xiên 3, xiên 4, xiên 5, xiên 6 (Miền Bắc)
                $xienlevel = intval($xienMatches[1]);
                if ($xienlevel >= 2 && $xienlevel <= 6) {
                    $standardBetType = 'xien' . $xienlevel;
                } else {
                    $result['error'] = "Kiểu đánh xiên không hợp lệ: {$betType}";
                    return $result;
                }
            } else {
                $result['error'] = "Kiểu đánh không được hỗ trợ: {$betType}";
                return $result;
            }
            
            // Tìm betType trong database
            $betTypeModel = BetType::where('code', $standardBetType)->first();
            
            if ($betTypeModel) {
                $result['bet_type_id'] = $betTypeModel->id;
                
                // Áp dụng cài đặt giá cả
                if ($customer && $customer->setting) {
                    $settings = $customer->setting;
                    $payoutRatio = $this->getPayoutRatio($standardBetType, $region, $settings);
                    $result['payout_ratio'] = $payoutRatio;
                } else {
                    // Nếu không có khách hàng hoặc không có cài đặt, sử dụng tỷ lệ mặc định
                    $result['payout_ratio'] = $betTypeModel->payout_ratio;
                    Log::debug($betTypeModel->payout_ratio);
                }
            } else {
                $result['error'] = "Không tìm thấy kiểu đánh {$betType} trong hệ thống";
                return $result;
            }
            
            // Tính tiền thắng tiềm năng
            if (isset($result['payout_ratio'])) {
                $result['potential_win'] = $result['amount'] * $result['payout_ratio'];
            }
            
            $result['is_valid'] = true;
        }
        
        return $result;
    }
    
    // Hàm lấy tỷ lệ thắng dựa vào loại cược và vùng
    private function getPayoutRatio($betType, $region, $settings)
    {
        // Tỷ lệ thắng dựa vào cài đặt của khách hàng
        switch ($region) {
            case 'mn': // Miền Nam
                if ($betType == 'de') {
                    return $settings->south_head_tail_win;
                } elseif ($betType == 'lo') {
                    Log::debug("Using south_lo_win: {$settings->south_lo_win}");
                    return $settings->south_lo_win;
                } elseif ($betType == '3c') {
                    return $settings->south_3_digits_win;
                } elseif ($betType == '4c') {
                    return $settings->south_4_digits_win;
                } elseif ($betType == 'da_xien') {
                    return $settings->south_slide_win;
                } elseif ($betType == 'da_thang') {
                    return $settings->south_straight_win;
                }
                break;
                
            case 'mb': // Miền Bắc
                if ($betType == 'de') {
                    return $settings->north_head_tail_win;
                } elseif ($betType == 'lo') {
                    return $settings->north_lo_win;
                } elseif ($betType == '3c') {
                    return $settings->north_3_digits_win;
                } elseif ($betType == '4c') {
                    return $settings->north_4_digits_win;
                } elseif ($betType == 'da') {
                    return $settings->north_slide_win;
                } elseif ($betType == 'xien2') {
                    return $settings->north_slide2_win;
                } elseif ($betType == 'xien3') {
                    return $settings->north_slide3_win;
                } elseif ($betType == 'xien4') {
                    return $settings->north_slide4_win;
                } elseif ($betType == 'xien5') {
                    return $settings->north_slide5_win;
                } elseif ($betType == 'xien6') {
                    return $settings->north_slide6_win;
                }
                break;
                
            case 'mt': // Miền Trung
                if ($betType == 'de') {
                    return $settings->central_head_tail_win;
                } elseif ($betType == 'lo') {
                    return $settings->central_lo_win;
                } elseif ($betType == '3c') {
                    return $settings->central_3_digits_win;
                } elseif ($betType == '4c') {
                    return $settings->central_4_digits_win;
                } elseif ($betType == 'da_xien') {
                    return $settings->central_slide_win;
                } elseif ($betType == 'da_thang') {
                    return $settings->central_straight_win;
                }
                break;
        }
        
        // Giá trị mặc định nếu không tìm thấy cài đặt
        return 1.0;
    }
    
    private function getVietnameseDayOfWeek($dayOfWeek)
    {
        $days = [
            0 => 'Chủ nhật',
            1 => 'Thứ hai',
            2 => 'Thứ ba',
            3 => 'Thứ tư',
            4 => 'Thứ năm',
            5 => 'Thứ sáu',
            6 => 'Thứ bảy'
        ];
        
        return $days[$dayOfWeek] ?? '';
    }

    /**
     * Chuẩn hóa kiểu đánh từ cú pháp đầu vào
     */
    private function normalizeBetType($betType, $region)
    {
        $betType = strtolower($betType);
        
        // Chuyển đổi các mã kiểu đánh thành mã tiêu chuẩn
        if (in_array($betType, ['de', 'dt'])) {
            return 'de'; // Đề
        } elseif ($betType == 'lo') {
            return 'lo'; // Lô
        } elseif ($betType == '3c') {
            return '3c'; // 3 càng
        } elseif ($betType == '4c') {
            return '4c'; // 4 càng
        } elseif ($betType == 'dau') {
            return 'dau'; // Số đầu
        } elseif ($betType == 'duoi') {
            return 'duoi'; // Số đuôi
        } elseif (in_array($betType, ['da', 'dt', 'dx'])) {
            if ($region == 'mb') {
                return 'da'; // Đá (Miền Bắc)
            } else {
                // Phân biệt đá xiên và đá thẳng
                if (strpos($betType, 'dt') !== false) {
                    return 'da_thang'; // Đá thẳng (Miền Nam/Trung)
                } else {
                    return 'da_xien'; // Đá xiên (Miền Nam/Trung)
                }
            }
        } elseif (preg_match('/xien(\d)/i', $betType, $matches) || preg_match('/x(\d)/i', $betType, $matches)) {
            // Xử lý xiên 2, xiên 3, xiên 4, xiên 5, xiên 6 (Miền Bắc)
            $xienLevel = intval($matches[1]);
            if ($xienLevel >= 2 && $xienLevel <= 6) {
                return 'xien' . $xienLevel;
            }
        }
        
        // Mặc định trả về đúng kiểu đánh đã nhập
        return $betType;
    }
}