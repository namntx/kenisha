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
        // Tạo cache key
        $cacheKey = "lottery_results:{$date->format('Y-m-d')}:{$region}" . ($province ? ":{$province}" : "");
        
        // Kiểm tra cache
        if (Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }
        
        // Lấy kết quả từ nguồn xosothantai.mobi
        $results = $this->fetchFromXoSoThanTai($date, $region, $province);
        
        // Nếu có kết quả, lưu vào cache
        if ($results) {
            // Cache kết quả trong 24 giờ
            Cache::put($cacheKey, $results, now()->addHours(24));
        }
        
        return $results;
    }
    
    /**
     * Lấy kết quả xổ số từ nguồn xosothantai.mobi
     */
    private function fetchFromXoSoThanTai(Carbon $date, string $region, ?string $province = null)
    {
        try {
            $dateStr = $date->format('d-m-Y');
            $formattedDate = $date->format('d-m-Y');
            $day = $date->format('d');
            $month = $date->format('m');
            $year = $date->format('Y');
            
            // Xác định URL dựa vào vùng miền
            $url = '';
            if ($region === 'mb') {
                $url = "https://xosothantai.mobi/embedded/kq-mienbac#n{$day}-{$month}-{$year}";
            } elseif ($region === 'mn') {
                $url = "https://xosothantai.mobi/embedded/kq-miennam#n{$day}-{$month}-{$year}";
            } elseif ($region === 'mt') {
                $url = "https://xosothantai.mobi/embedded/kq-mientrung#n{$day}-{$month}-{$year}";
            }
            
            $response = $this->client->get($url, [
                'headers' => [
                    'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/133.0.0.0 Safari/537.36',
                    'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.7',
                    'Accept-Language' => 'vi-VN,vi;q=0.9,en-US;q=0.8,en;q=0.7',
                    'Cache-Control' => 'no-cache',
                    'Pragma' => 'no-cache',
                    'Referer' => 'https://xosothantai.mobi/'
                ]
            ]);
            
            if ($response->getStatusCode() != 200) {
                Log::warning('Failed to fetch lottery results from xosothantai.mobi', [
                    'date' => $dateStr,
                    'region' => $region,
                    'province' => $province,
                    'status_code' => $response->getStatusCode()
                ]);
                return null;
            }
            
            $html = (string) $response->getBody();
            
            // Log response để debug
            Log::debug('Response from xosothantai.mobi', [
                'url' => $url,
                'status_code' => $response->getStatusCode(),
                'headers' => $response->getHeaders(),
                'body_length' => strlen($html),
                'body_preview' => substr($html, 0, 500)
            ]);
            
            // Chuyển đổi encoding
            $html = mb_convert_encoding($html, 'HTML-ENTITIES', 'UTF-8');
            
            // Parse kết quả dựa trên vùng miền
            $results = null;
            switch ($region) {
                case 'mb':
                    $results = $this->parseXoSoThanTaiMienBacResults($html);
                    break;
                case 'mn':
                    $results = $this->parseXoSoThanTaiMienNamResults($html, $province);
                    break;
                case 'mt':
                    $results = $this->parseXoSoThanTaiMienTrungResults($html, $province);
                    break;
                default:
                    Log::warning('Invalid region code', ['region' => $region]);
                    return null;
            }
            
            // Kiểm tra kết quả có hợp lệ không
            if (!$this->validateResults($results)) {
                Log::warning('Invalid results format from xosothantai.mobi', [
                    'date' => $date->format('Y-m-d'),
                    'region' => $region,
                    'province' => $province,
                    'results' => $results
                ]);
                return null;
            }
            
            return $results;
        } catch (\Exception $e) {
            Log::error('Error fetching lottery results from xosothantai.mobi: ' . $e->getMessage(), [
                'date' => $date->format('Y-m-d'),
                'region' => $region,
                'province' => $province,
                'exception' => $e
            ]);
            
            return null;
        }
    }

    /**
     * Parse kết quả Miền Bắc từ xosothantai.mobi
     */
    private function parseXoSoThanTaiMienBacResults($html)
    {
        $dom = new DOMDocument();
        libxml_use_internal_errors(true);
        @$dom->loadHTML($html);
        libxml_clear_errors();
        $xpath = new DOMXPath($dom);
        
        $results = [
            'special_prize' => '',
            'first_prize' => '',
            'second_prize' => [],
            'third_prize' => [],
            'fourth_prize' => [],
            'fifth_prize' => [],
            'sixth_prize' => [],
            'seventh_prize' => [],
        ];
        
        // Tìm bảng kết quả
        $table = $xpath->query("//table[contains(@class, 'table-result')]")->item(0);

        dd($table);
        
        if ($table) {
            // Lấy các giải thưởng
            $prizes = [
                'special_prize' => ".//tr[contains(@class, 'gdb')]//td[2]",
                'first_prize' => ".//tr[contains(@class, 'g1')]//td[2]",
                'second_prize' => ".//tr[contains(@class, 'g2')]//td[2]",
                'third_prize' => ".//tr[contains(@class, 'g3')]//td[2]",
                'fourth_prize' => ".//tr[contains(@class, 'g4')]//td[2]",
                'fifth_prize' => ".//tr[contains(@class, 'g5')]//td[2]",
                'sixth_prize' => ".//tr[contains(@class, 'g6')]//td[2]",
                'seventh_prize' => ".//tr[contains(@class, 'g7')]//td[2]",
            ];
            
            foreach ($prizes as $key => $xpath_query) {
                $nodes = $xpath->query($xpath_query, $table);
                foreach ($nodes as $node) {
                    $number = trim($node->textContent);
                    if ($number) {
                        if (in_array($key, ['special_prize', 'first_prize'])) {
                            $results[$key] = $number;
                        } else {
                            $results[$key][] = $number;
                        }
                    }
                }
            }
            
            return $results;
        }
        
        return null;
    }

    /**
     * Parse kết quả Miền Nam từ xosothantai.mobi
     */
    private function parseXoSoThanTaiMienNamResults($html, $province = null)
    {
        $dom = new DOMDocument();
        libxml_use_internal_errors(true);
        @$dom->loadHTML($html);
        libxml_clear_errors();
        $xpath = new DOMXPath($dom);
        
        $results = [
            'eighth_prize' => '',
            'seventh_prize' => '',
            'sixth_prize' => [],
            'fifth_prize' => '',
            'fourth_prize' => [],
            'third_prize' => [],
            'second_prize' => '',
            'first_prize' => '',
            'special_prize' => '',
        ];
        
        // Tìm bảng kết quả cho tỉnh cụ thể
        $table = null;
        if ($province) {
            $tables = $xpath->query("//table[contains(@class, 'table-result')]");
            foreach ($tables as $t) {
                $header = $xpath->query(".//tr[contains(@class, 'tentinh')]", $t)->item(0);
                if ($header && str_contains(strtolower($header->textContent), strtolower($province))) {
                    $table = $t;
                    break;
                }
            }
        } else {
            $table = $xpath->query("//table[contains(@class, 'table-result')]")->item(0);
        }
        
        if ($table) {
            // Lấy các giải thưởng
            $prizes = [
                'eighth_prize' => ".//tr[contains(@class, 'g8')]//td[2]",
                'seventh_prize' => ".//tr[contains(@class, 'g7')]//td[2]",
                'sixth_prize' => ".//tr[contains(@class, 'g6')]//td[2]",
                'fifth_prize' => ".//tr[contains(@class, 'g5')]//td[2]",
                'fourth_prize' => ".//tr[contains(@class, 'g4')]//td[2]",
                'third_prize' => ".//tr[contains(@class, 'g3')]//td[2]",
                'second_prize' => ".//tr[contains(@class, 'g2')]//td[2]",
                'first_prize' => ".//tr[contains(@class, 'g1')]//td[2]",
                'special_prize' => ".//tr[contains(@class, 'gdb')]//td[2]",
            ];
            
            foreach ($prizes as $key => $xpath_query) {
                $nodes = $xpath->query($xpath_query, $table);
                foreach ($nodes as $node) {
                    $number = trim($node->textContent);
                    if ($number) {
                        if (in_array($key, ['special_prize', 'first_prize', 'second_prize', 'fifth_prize', 'seventh_prize', 'eighth_prize'])) {
                            $results[$key] = $number;
                        } else {
                            $results[$key][] = $number;
                        }
                    }
                }
            }
            
            return $results;
        }
        
        return null;
    }

    /**
     * Parse kết quả Miền Trung từ xosothantai.mobi
     */
    private function parseXoSoThanTaiMienTrungResults($html, $province = null)
    {
        // Sử dụng cùng logic với Miền Nam vì cấu trúc tương tự
        return $this->parseXoSoThanTaiMienNamResults($html, $province);
    }
    
    /**
     * Kiểm tra xem kết quả đã có hoặc đã đủ chưa
     */
    public function isResultComplete($results)
    {
        // Kiểm tra các giải quan trọng
        if (empty($results['special_prize']) || empty($results['first_prize'])) {
            return false;
        }
        
        // Kiểm tra số lượng giải
        if (
            (isset($results['second_prize']) && count($results['second_prize']) < 1) ||
            (isset($results['third_prize']) && count($results['third_prize']) < 1) ||
            (isset($results['fourth_prize']) && count($results['fourth_prize']) < 1)
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
    
    /**
     * Validate kết quả
     */
    private function validateResults($results)
    {
        // Kiểm tra các giải thưởng
        $requiredPrizes = ['special_prize', 'first_prize', 'second_prize', 'third_prize', 'fourth_prize', 'fifth_prize', 'sixth_prize', 'seventh_prize'];
        foreach ($requiredPrizes as $prize) {
            if (!isset($results[$prize])) {
                return false;
            }
        }
        
        // Kiểm tra số lượng giải
        if (count($results['second_prize']) < 1 || count($results['third_prize']) < 1 || count($results['fourth_prize']) < 1) {
            return false;
        }
        
        return true;
    }
}