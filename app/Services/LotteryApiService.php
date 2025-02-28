<?php

namespace App\Services;

use GuzzleHttp\Client;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use DOMDocument;
use DOMXPath;

class LotteryApiService
{
    protected $client;
    
    public function __construct()
    {
        $this->client = new Client([
            'timeout' => 10,
            'http_errors' => false,
            'verify' => false
        ]);
    }
    
    /**
     * Lấy kết quả xổ số cho một ngày và vùng miền cụ thể
     *
     * @param Carbon $date Ngày cần lấy kết quả
     * @param string $region Mã vùng miền (mb, mn, mt)
     * @param string $province Mã tỉnh (tùy chọn)
     * @return array|null Kết quả xổ số hoặc null nếu không có dữ liệu
     */
    public function getResults(Carbon $date, string $region, ?string $province = null)
    {
        // Chuẩn hóa mã vùng miền
        $region = strtolower($region);
        
        // Tạo cache key
        $cacheKey = "lottery_results:{$region}:{$date->format('Y-m-d')}";
        if ($province) {
            $cacheKey .= ":{$province}";
        }
        
        // Kiểm tra cache trước
        if (Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }
        
        // Lấy kết quả từ nguồn chính (xoso.com.vn)
        $results = $this->fetchFromXosoComVn($date, $region, $province);
        
        // Nếu có kết quả, lưu vào cache
        if ($results) {
            // Cache kết quả trong 24 giờ
            Cache::put($cacheKey, $results, now()->addHours(24));
        }
        
        return $results;
    }
    
    /**
     * Lấy kết quả từ nguồn xoso.com.vn
     */
    private function fetchFromXosoComVn(Carbon $date, string $region, ?string $province = null)
    {
        try {
            $dateStr = $date->format('Y-m-d');
            $url = "https://xoso.com.vn/kqxs/{$region}-{$dateStr}.html";
            
            $response = $this->client->get($url);
            
            if ($response->getStatusCode() != 200) {
                Log::warning('Failed to fetch lottery results from xoso.com.vn', [
                    'date' => $dateStr,
                    'region' => $region,
                    'province' => $province,
                    'status_code' => $response->getStatusCode()
                ]);
                return null;
            }
            
            $html = (string) $response->getBody();
            
            // Tìm bảng kết quả dựa trên HTML
            switch ($region) {
                case 'mb':
                    return $this->parseMienBacResults($html);
                case 'mn':
                    return $this->parseMienNamResults($html, $province);
                case 'mt':
                    return $this->parseMienTrungResults($html, $province);
                default:
                    Log::warning('Invalid region code', ['region' => $region]);
                    return null;
            }
        } catch (\Exception $e) {
            Log::error('Error fetching lottery results from xoso.com.vn: ' . $e->getMessage(), [
                'date' => $date->format('Y-m-d'),
                'region' => $region,
                'province' => $province,
                'exception' => $e
            ]);
            
            return null;
        }
    }
    
    /**
     * Phân tích kết quả Miền Bắc từ xoso.com.vn
     */
    private function parseMienBacResults($html)
    {
        // Tạo DOMDocument để parse HTML
        $dom = new DOMDocument();
        @$dom->loadHTML($html);
        $xpath = new DOMXPath($dom);
        
        // Tìm bảng kết quả
        $table = $xpath->query('//table[contains(@class, "table-result")]')->item(0);
        
        if (!$table) {
            Log::warning('Could not find result table for Mien Bac');
            return null;
        }
        
        $results = [];
        
        // Lấy giải đặc biệt
        $specialPrize = $xpath->query('.//tr[contains(@class, "db")]//td[contains(@class, "number")]', $table)->item(0);
        if ($specialPrize) {
            $results['special'] = trim($specialPrize->textContent);
        }
        
        // Lấy giải nhất
        $firstPrize = $xpath->query('.//tr[contains(@class, "g1")]//td[contains(@class, "number")]', $table)->item(0);
        if ($firstPrize) {
            $results['first'] = trim($firstPrize->textContent);
        }
        
        // Lấy giải nhì
        $secondPrizes = $xpath->query('.//tr[contains(@class, "g2")]//td[contains(@class, "number")]', $table);
        if ($secondPrizes->length > 0) {
            $results['second'] = [];
            foreach ($secondPrizes as $prize) {
                $results['second'][] = trim($prize->textContent);
            }
        }
        
        // Lấy giải ba
        $thirdPrizes = $xpath->query('.//tr[contains(@class, "g3")]//td[contains(@class, "number")]', $table);
        if ($thirdPrizes->length > 0) {
            $results['third'] = [];
            foreach ($thirdPrizes as $prize) {
                $results['third'][] = trim($prize->textContent);
            }
        }
        
        // Lấy giải tư
        $fourthPrizes = $xpath->query('.//tr[contains(@class, "g4")]//td[contains(@class, "number")]', $table);
        if ($fourthPrizes->length > 0) {
            $results['fourth'] = [];
            foreach ($fourthPrizes as $prize) {
                $results['fourth'][] = trim($prize->textContent);
            }
        }
        
        // Lấy giải năm
        $fifthPrizes = $xpath->query('.//tr[contains(@class, "g5")]//td[contains(@class, "number")]', $table);
        if ($fifthPrizes->length > 0) {
            $results['fifth'] = [];
            foreach ($fifthPrizes as $prize) {
                $results['fifth'][] = trim($prize->textContent);
            }
        }
        
        // Lấy giải sáu
        $sixthPrizes = $xpath->query('.//tr[contains(@class, "g6")]//td[contains(@class, "number")]', $table);
        if ($sixthPrizes->length > 0) {
            $results['sixth'] = [];
            foreach ($sixthPrizes as $prize) {
                $results['sixth'][] = trim($prize->textContent);
            }
        }
        
        // Lấy giải bảy
        $seventhPrizes = $xpath->query('.//tr[contains(@class, "g7")]//td[contains(@class, "number")]', $table);
        if ($seventhPrizes->length > 0) {
            $results['seventh'] = [];
            foreach ($seventhPrizes as $prize) {
                $results['seventh'][] = trim($prize->textContent);
            }
        }
        
        return $results;
    }
    
    /**
     * Phân tích kết quả Miền Nam từ xoso.com.vn
     */
    private function parseMienNamResults($html, $province = null)
    {
        // Tạo DOMDocument để parse HTML
        $dom = new DOMDocument();
        @$dom->loadHTML($html);
        $xpath = new DOMXPath($dom);
        
        // Tìm bảng kết quả
        $tables = $xpath->query('//table[contains(@class, "table-result")]');
        
        if ($tables->length === 0) {
            Log::warning('Could not find result tables for Mien Nam');
            return null;
        }
        
        // Nếu có mã tỉnh, chỉ lấy kết quả của tỉnh đó
        if ($province) {
            $provinceTable = null;
            
            // Tìm bảng chứa kết quả của tỉnh
            foreach ($tables as $table) {
                $title = $xpath->query('.//div[contains(@class, "title-bkqxs")]', $table)->item(0);
                if ($title && stripos($title->textContent, $province) !== false) {
                    $provinceTable = $table;
                    break;
                }
            }
            
            if (!$provinceTable) {
                Log::warning('Could not find result table for specified province', ['province' => $province]);
                return null;
            }
            
            // Phân tích kết quả từ bảng của tỉnh
            return $this->parseSingleTable($provinceTable, $xpath);
        }
        
        // Nếu không có mã tỉnh, lấy kết quả từ bảng đầu tiên
        return $this->parseSingleTable($tables->item(0), $xpath);
    }
    
    /**
     * Phân tích kết quả Miền Trung từ xoso.com.vn
     */
    private function parseMienTrungResults($html, $province = null)
    {
        // Tương tự như phân tích Miền Nam
        return $this->parseMienNamResults($html, $province);
    }
    
    /**
     * Phân tích kết quả từ một bảng xổ số đơn lẻ
     */
    private function parseSingleTable($table, $xpath)
    {
        if (!$table) {
            return null;
        }
        
        $results = [];
        
        // Lấy giải đặc biệt
        $specialPrize = $xpath->query('.//tr[contains(@class, "db")]//td[contains(@class, "number")]', $table)->item(0);
        if ($specialPrize) {
            $results['special'] = trim($specialPrize->textContent);
        }
        
        // Lấy giải nhất
        $firstPrize = $xpath->query('.//tr[contains(@class, "g1")]//td[contains(@class, "number")]', $table)->item(0);
        if ($firstPrize) {
            $results['first'] = trim($firstPrize->textContent);
        }
        
        // Lấy giải nhì
        $secondPrizes = $xpath->query('.//tr[contains(@class, "g2")]//td[contains(@class, "number")]', $table);
        if ($secondPrizes->length > 0) {
            $results['second'] = [];
            foreach ($secondPrizes as $prize) {
                $results['second'][] = trim($prize->textContent);
            }
        }
        
        // Lấy giải ba
        $thirdPrizes = $xpath->query('.//tr[contains(@class, "g3")]//td[contains(@class, "number")]', $table);
        if ($thirdPrizes->length > 0) {
            $results['third'] = [];
            foreach ($thirdPrizes as $prize) {
                $results['third'][] = trim($prize->textContent);
            }
        }
        
        // Lấy giải tư
        $fourthPrizes = $xpath->query('.//tr[contains(@class, "g4")]//td[contains(@class, "number")]', $table);
        if ($fourthPrizes->length > 0) {
            $results['fourth'] = [];
            foreach ($fourthPrizes as $prize) {
                $results['fourth'][] = trim($prize->textContent);
            }
        }
        
        // Lấy giải năm
        $fifthPrizes = $xpath->query('.//tr[contains(@class, "g5")]//td[contains(@class, "number")]', $table);
        if ($fifthPrizes->length > 0) {
            $results['fifth'] = [];
            foreach ($fifthPrizes as $prize) {
                $results['fifth'][] = trim($prize->textContent);
            }
        }
        
        // Lấy giải sáu
        $sixthPrizes = $xpath->query('.//tr[contains(@class, "g6")]//td[contains(@class, "number")]', $table);
        if ($sixthPrizes->length > 0) {
            $results['sixth'] = [];
            foreach ($sixthPrizes as $prize) {
                $results['sixth'][] = trim($prize->textContent);
            }
        }
        
        // Lấy giải bảy
        $seventhPrizes = $xpath->query('.//tr[contains(@class, "g7")]//td[contains(@class, "number")]', $table);
        if ($seventhPrizes->length > 0) {
            $results['seventh'] = [];
            foreach ($seventhPrizes as $prize) {
                $results['seventh'][] = trim($prize->textContent);
            }
        }
        
        // Lấy giải tám (nếu có)
        $eighthPrizes = $xpath->query('.//tr[contains(@class, "g8")]//td[contains(@class, "number")]', $table);
        if ($eighthPrizes->length > 0) {
            $results['eighth'] = [];
            foreach ($eighthPrizes as $prize) {
                $results['eighth'][] = trim($prize->textContent);
            }
        }
        
        return $results;
    }
    
    /**
     * Kiểm tra xem kết quả đã có hoặc đã đủ chưa
     */
    public function isResultComplete($results)
    {
        // Kiểm tra các giải quan trọng
        if (empty($results['special']) || empty($results['first'])) {
            return false;
        }
        
        // Kiểm tra số lượng giải
        if (
            (isset($results['second']) && count($results['second']) < 1) ||
            (isset($results['third']) && count($results['third']) < 1) ||
            (isset($results['fourth']) && count($results['fourth']) < 1)
        ) {
            return false;
        }
        
        return true;
    }
    
    /**
     * Làm sạch kết quả, loại bỏ các ký tự không phải số
     */
    public function cleanResults($results)
    {
        if (!is_array($results)) {
            return null;
        }
        
        $cleanedResults = [];
        
        foreach ($results as $key => $value) {
            if (is_array($value)) {
                $cleanedResults[$key] = [];
                foreach ($value as $subValue) {
                    // Chỉ giữ lại các ký tự số
                    $cleanedResults[$key][] = preg_replace('/[^0-9]/', '', $subValue);
                }
            } else {
                // Chỉ giữ lại các ký tự số
                $cleanedResults[$key] = preg_replace('/[^0-9]/', '', $value);
            }
        }
        
        return $cleanedResults;
    }
    
    /**
     * Trích xuất tất cả các số trong kết quả
     */
    public function extractAllNumbers($results, $digits = 2)
    {
        $allNumbers = [];
        
        foreach ($results as $key => $value) {
            if (is_array($value)) {
                foreach ($value as $number) {
                    if (strlen($number) >= $digits) {
                        $lastDigits = substr($number, -$digits);
                        $allNumbers[] = $lastDigits;
                    }
                }
            } else {
                if (strlen($value) >= $digits) {
                    $lastDigits = substr($value, -$digits);
                    $allNumbers[] = $lastDigits;
                }
            }
        }
        
        return array_unique($allNumbers);
    }
    
    /**
     * Kiểm tra xem một số có xuất hiện trong kết quả không
     */
    public function isNumberInResults($number, $results, $digits = 2)
    {
        $allNumbers = $this->extractAllNumbers($results, $digits);
        return in_array($number, $allNumbers);
    }
    
    /**
     * Đếm số lần xuất hiện của một số trong kết quả
     */
    public function countNumberOccurrences($number, $results, $digits = 2)
    {
        $count = 0;
        
        foreach ($results as $key => $value) {
            if (is_array($value)) {
                foreach ($value as $prize) {
                    if (strlen($prize) >= $digits) {
                        $lastDigits = substr($prize, -$digits);
                        if ($lastDigits === $number) {
                            $count++;
                        }
                    }
                }
            } else {
                if (strlen($value) >= $digits) {
                    $lastDigits = substr($value, -$digits);
                    if ($lastDigits === $number) {
                        $count++;
                    }
                }
            }
        }
        
        return $count;
    }
}