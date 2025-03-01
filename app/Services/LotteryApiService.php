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
            $day = $date->format('d');
            $month = $date->format('m');
            $year = $date->format('Y');
            
            // Tạo chuỗi ngày cho tham số AJAX
            $urlDateFragment = "{$day}-{$month}-{$year}";
            
            // Xác định URL endpoint AJAX dựa vào vùng miền
            $ajaxUrl = '';
            if ($region === 'mb') {
                $ajaxUrl = 'https://xosothantai.mobi/embedded/kq-mienbac';
            } elseif ($region === 'mn') {
                $ajaxUrl = 'https://xosothantai.mobi/embedded/kq-miennam';
            } elseif ($region === 'mt') {
                $ajaxUrl = 'https://xosothantai.mobi/embedded/kq-mientrung';
            }
            
            // Gửi POST request đến endpoint AJAX
            $response = $this->client->post($ajaxUrl, [
                'headers' => [
                    'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/133.0.0.0 Safari/537.36',
                    'Accept' => 'application/json, text/javascript, */*; q=0.01',
                    'Accept-Language' => 'vi-VN,vi;q=0.9,en-US;q=0.8,en;q=0.7',
                    'Cache-Control' => 'no-cache',
                    'Pragma' => 'no-cache',
                    'Referer' => 'https://xosothantai.mobi/',
                    'X-Requested-With' => 'XMLHttpRequest',
                    'Content-Type' => 'application/x-www-form-urlencoded; charset=UTF-8',
                    'Origin' => 'https://xosothantai.mobi'
                ],
                'form_params' => [
                    'ngay_quay' => $urlDateFragment
                ]
            ]);

            
            if ($response->getStatusCode() != 200) {
                Log::warning('Failed to fetch lottery results from xosothantai.mobi', [
                    'date' => $date->format('Y-m-d'),
                    'region' => $region,
                    'province' => $province,
                    'status_code' => $response->getStatusCode()
                ]);
                return null;
            }
            
            // Phân tích kết quả JSON
            $jsonResponse = json_decode((string) $response->getBody(), true);
            
            // Log response để debug
            Log::debug('Response from xosothantai.mobi', [
                'url' => $ajaxUrl,
                'status_code' => $response->getStatusCode(),
                'json_status' => $jsonResponse['stt'] ?? 'unknown',
                'has_data' => isset($jsonResponse['data']) ? 'yes' : 'no'
            ]);
            
            // Kiểm tra xem có dữ liệu hợp lệ không
            if (!isset($jsonResponse['stt']) || $jsonResponse['stt'] != 1 || !isset($jsonResponse['data'])) {
                Log::warning('Invalid JSON response from xosothantai.mobi', [
                    'date' => $date->format('Y-m-d'),
                    'region' => $region,
                    'province' => $province,
                    'response' => $jsonResponse
                ]);
                return null;
            }
            
            // Lấy HTML từ phản hồi JSON
            $html = $jsonResponse['data'];
            
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
            if (!$this->validateResults($results, $region)) {
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
        
        // Tìm bảng kết quả - class kqmb là class chính của bảng kết quả miền bắc
        $table = $xpath->query("//table[contains(@class, 'kqmb')]")->item(0);
        
        if ($table) {
            // Lấy giải đặc biệt - sử dụng class v-gdb
            $specialNodes = $xpath->query(".//span[contains(@class, 'v-gdb')]", $table);
            if ($specialNodes->length > 0) {
                $results['special_prize'] = trim($specialNodes->item(0)->textContent);
            }
            
            // Lấy giải nhất - sử dụng class v-g1
            $firstNodes = $xpath->query(".//span[contains(@class, 'v-g1')]", $table);
            if ($firstNodes->length > 0) {
                $results['first_prize'] = trim($firstNodes->item(0)->textContent);
            }
            
            // Lấy giải nhì - sử dụng class v-g2-0, v-g2-1, etc.
            $secondNodes = $xpath->query(".//span[starts-with(@class, 'v-g2-')]", $table);
            foreach ($secondNodes as $node) {
                $number = trim($node->textContent);
                if ($number) {
                    $results['second_prize'][] = $number;
                }
            }
            
            // Lấy giải ba - sử dụng class v-g3-0, v-g3-1, etc.
            $thirdNodes = $xpath->query(".//span[starts-with(@class, 'v-g3-')]", $table);
            foreach ($thirdNodes as $node) {
                $number = trim($node->textContent);
                if ($number) {
                    $results['third_prize'][] = $number;
                }
            }
            
            // Lấy giải tư - sử dụng class v-g4-0, v-g4-1, etc.
            $fourthNodes = $xpath->query(".//span[starts-with(@class, 'v-g4-')]", $table);
            foreach ($fourthNodes as $node) {
                $number = trim($node->textContent);
                if ($number) {
                    $results['fourth_prize'][] = $number;
                }
            }
            
            // Lấy giải năm - sử dụng class v-g5-0, v-g5-1, etc.
            $fifthNodes = $xpath->query(".//span[starts-with(@class, 'v-g5-')]", $table);
            foreach ($fifthNodes as $node) {
                $number = trim($node->textContent);
                if ($number) {
                    $results['fifth_prize'][] = $number;
                }
            }
            
            // Lấy giải sáu - sử dụng class v-g6-0, v-g6-1, etc.
            $sixthNodes = $xpath->query(".//span[starts-with(@class, 'v-g6-')]", $table);
            foreach ($sixthNodes as $node) {
                $number = trim($node->textContent);
                if ($number) {
                    $results['sixth_prize'][] = $number;
                }
            }
            
            // Lấy giải bảy - sử dụng class v-g7-0, v-g7-1, etc.
            $seventhNodes = $xpath->query(".//span[starts-with(@class, 'v-g7-')]", $table);
            foreach ($seventhNodes as $node) {
                $number = trim($node->textContent);
                if ($number) {
                    $results['seventh_prize'][] = $number;
                }
            }
            
            // Làm sạch kết quả, loại bỏ các ký tự không phải số
            foreach ($results as $key => $value) {
                if (is_array($value)) {
                    foreach ($value as $k => $v) {
                        $results[$key][$k] = preg_replace('/[^0-9]/', '', $v);
                    }
                } else {
                    $results[$key] = preg_replace('/[^0-9]/', '', $value);
                }
            }
        }
        
        return $results;
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
        $table = $xpath->query("//table[contains(@class, 'colthreecity') or contains(@class, 'coltwocity') or contains(@class, 'colgiai')]")->item(0);
        
        if (!$table) {
            // Thử tìm bằng class khác
            $table = $xpath->query("//table[contains(@class, 'kqmn')]")->item(0);
        }
        
        if ($table && $province) {
            // Chuyển đổi mã tỉnh thành tên tỉnh để so sánh
            $provinceName = $this->getProvinceNameByCode($province, 3); // 3 là region_id cho miền Nam
            
            // Tìm cột tương ứng với tỉnh
            $provinceIndex = -1;
            $provinceHeaders = $xpath->query(".//th[contains(@data-pid, '')]/a", $table);
            
            for ($i = 0; $i < $provinceHeaders->length; $i++) {
                $header = $provinceHeaders->item($i);
                if (stripos($header->textContent, $provinceName) !== false) {
                    $provinceIndex = $i;
                    break;
                }
            }
            
            if ($provinceIndex >= 0) {
                // Lấy giải đặc biệt (ĐB)
                $specialNodes = $xpath->query(".//tr[contains(@class, 'gdb')]/td[" . ($provinceIndex + 2) . "]//div[contains(@class, 'v-gdb')]", $table);
                if ($specialNodes->length > 0) {
                    $results['special_prize'] = trim($specialNodes->item(0)->textContent);
                }
                
                // Lấy giải nhất (G1)
                $firstNodes = $xpath->query(".//tr[td[text()='G1']]/td[" . ($provinceIndex + 2) . "]//div[contains(@class, 'v-g1')]", $table);
                if ($firstNodes->length > 0) {
                    $results['first_prize'] = trim($firstNodes->item(0)->textContent);
                }
                
                // Lấy giải nhì (G2)
                $secondNodes = $xpath->query(".//tr[td[text()='G2']]/td[" . ($provinceIndex + 2) . "]//div[contains(@class, 'v-g2')]", $table);
                if ($secondNodes->length > 0) {
                    $results['second_prize'] = trim($secondNodes->item(0)->textContent);
                }
                
                // Lấy giải ba (G3)
                $thirdNodes = $xpath->query(".//tr[td[text()='G3']]/td[" . ($provinceIndex + 2) . "]//div[starts-with(@class, 'v-g3-')]", $table);
                foreach ($thirdNodes as $node) {
                    $number = trim($node->textContent);
                    if ($number) {
                        $results['third_prize'][] = $number;
                    }
                }
                
                // Lấy giải tư (G4)
                $fourthNodes = $xpath->query(".//tr[td[text()='G4']]/td[" . ($provinceIndex + 2) . "]//div[starts-with(@class, 'v-g4-')]", $table);
                foreach ($fourthNodes as $node) {
                    $number = trim($node->textContent);
                    if ($number) {
                        $results['fourth_prize'][] = $number;
                    }
                }
                
                // Lấy giải năm (G5)
                $fifthNodes = $xpath->query(".//tr[td[text()='G5']]/td[" . ($provinceIndex + 2) . "]//div[contains(@class, 'v-g5')]", $table);
                if ($fifthNodes->length > 0) {
                    $results['fifth_prize'] = trim($fifthNodes->item(0)->textContent);
                }
                
                // Lấy giải sáu (G6)
                $sixthNodes = $xpath->query(".//tr[td[text()='G6']]/td[" . ($provinceIndex + 2) . "]//div[starts-with(@class, 'v-g6-')]", $table);
                foreach ($sixthNodes as $node) {
                    $number = trim($node->textContent);
                    if ($number) {
                        $results['sixth_prize'][] = $number;
                    }
                }
                
                // Lấy giải bảy (G7)
                $seventhNodes = $xpath->query(".//tr[td[text()='G7']]/td[" . ($provinceIndex + 2) . "]//div[contains(@class, 'v-g7')]", $table);
                if ($seventhNodes->length > 0) {
                    $results['seventh_prize'] = trim($seventhNodes->item(0)->textContent);
                }
                
                // Lấy giải tám (G8)
                $eighthNodes = $xpath->query(".//tr[contains(@class, 'g8')]/td[" . ($provinceIndex + 2) . "]//div[contains(@class, 'v-g8')]", $table);
                if ($eighthNodes->length > 0) {
                    $results['eighth_prize'] = trim($eighthNodes->item(0)->textContent);
                }
            }
        } else if ($table) {
            // Nếu không có tỉnh cụ thể, lấy kết quả từ cột đầu tiên
            // Lấy giải đặc biệt (ĐB)
            $specialNodes = $xpath->query(".//tr[contains(@class, 'gdb')]/td[2]//div[contains(@class, 'v-gdb')]", $table);
            if ($specialNodes->length > 0) {
                $results['special_prize'] = trim($specialNodes->item(0)->textContent);
            }
            
            // Lấy giải nhất (G1)
            $firstNodes = $xpath->query(".//tr[td[text()='G1']]/td[2]//div[contains(@class, 'v-g1')]", $table);
            if ($firstNodes->length > 0) {
                $results['first_prize'] = trim($firstNodes->item(0)->textContent);
            }
            
            // Lấy giải nhì (G2)
            $secondNodes = $xpath->query(".//tr[td[text()='G2']]/td[2]//div[contains(@class, 'v-g2')]", $table);
            if ($secondNodes->length > 0) {
                $results['second_prize'] = trim($secondNodes->item(0)->textContent);
            }
            
            // Lấy giải ba (G3)
            $thirdNodes = $xpath->query(".//tr[td[text()='G3']]/td[2]//div[starts-with(@class, 'v-g3-')]", $table);
            foreach ($thirdNodes as $node) {
                $number = trim($node->textContent);
                if ($number) {
                    $results['third_prize'][] = $number;
                }
            }
            
            // Lấy giải tư (G4)
            $fourthNodes = $xpath->query(".//tr[td[text()='G4']]/td[2]//div[starts-with(@class, 'v-g4-')]", $table);
            foreach ($fourthNodes as $node) {
                $number = trim($node->textContent);
                if ($number) {
                    $results['fourth_prize'][] = $number;
                }
            }
            
            // Lấy giải năm (G5)
            $fifthNodes = $xpath->query(".//tr[td[text()='G5']]/td[2]//div[contains(@class, 'v-g5')]", $table);
            if ($fifthNodes->length > 0) {
                $results['fifth_prize'] = trim($fifthNodes->item(0)->textContent);
            }
            
            // Lấy giải sáu (G6)
            $sixthNodes = $xpath->query(".//tr[td[text()='G6']]/td[2]//div[starts-with(@class, 'v-g6-')]", $table);
            foreach ($sixthNodes as $node) {
                $number = trim($node->textContent);
                if ($number) {
                    $results['sixth_prize'][] = $number;
                }
            }
            
            // Lấy giải bảy (G7)
            $seventhNodes = $xpath->query(".//tr[td[text()='G7']]/td[2]//div[contains(@class, 'v-g7')]", $table);
            if ($seventhNodes->length > 0) {
                $results['seventh_prize'] = trim($seventhNodes->item(0)->textContent);
            }
            
            // Lấy giải tám (G8)
            $eighthNodes = $xpath->query(".//tr[contains(@class, 'g8')]/td[2]//div[contains(@class, 'v-g8')]", $table);
            if ($eighthNodes->length > 0) {
                $results['eighth_prize'] = trim($eighthNodes->item(0)->textContent);
            }
        }
        
        // Làm sạch kết quả, loại bỏ các ký tự không phải số
        foreach ($results as $key => $value) {
            if (is_array($value)) {
                foreach ($value as $k => $v) {
                    $results[$key][$k] = preg_replace('/[^0-9]/', '', $v);
                }
            } else {
                $results[$key] = preg_replace('/[^0-9]/', '', $value);
            }
        }
        
        return $results;
    }

    /**
     * Parse kết quả Miền Trung từ xosothantai.mobi
     */
    private function parseXoSoThanTaiMienTrungResults($html, $province = null)
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
        $table = $xpath->query("//table[contains(@class, 'colthreecity') or contains(@class, 'coltwocity') or contains(@class, 'colgiai')]")->item(0);
        
        if (!$table) {
            // Thử tìm bằng class khác
            $table = $xpath->query("//table[contains(@class, 'kqmt')]")->item(0);
        }
        
        if ($table && $province) {
            // Chuyển đổi mã tỉnh thành tên tỉnh để so sánh
            $provinceName = $this->getProvinceNameByCode($province, 2); // 2 là region_id cho miền Trung
            
            // Tìm cột tương ứng với tỉnh
            $provinceIndex = -1;
            $provinceHeaders = $xpath->query(".//th[contains(@data-pid, '')]/a", $table);
            
            for ($i = 0; $i < $provinceHeaders->length; $i++) {
                $header = $provinceHeaders->item($i);
                if (stripos($header->textContent, $provinceName) !== false) {
                    $provinceIndex = $i;
                    break;
                }
            }
            
            if ($provinceIndex >= 0) {
                // Lấy giải đặc biệt (ĐB)
                $specialNodes = $xpath->query(".//tr[contains(@class, 'gdb')]/td[" . ($provinceIndex + 2) . "]//div[contains(@class, 'v-gdb')]", $table);
                if ($specialNodes->length > 0) {
                    $results['special_prize'] = trim($specialNodes->item(0)->textContent);
                }
                
                // Lấy giải nhất (G1)
                $firstNodes = $xpath->query(".//tr[td[text()='G1']]/td[" . ($provinceIndex + 2) . "]//div[contains(@class, 'v-g1')]", $table);
                if ($firstNodes->length > 0) {
                    $results['first_prize'] = trim($firstNodes->item(0)->textContent);
                }
                
                // Lấy giải nhì (G2)
                $secondNodes = $xpath->query(".//tr[td[text()='G2']]/td[" . ($provinceIndex + 2) . "]//div[contains(@class, 'v-g2')]", $table);
                if ($secondNodes->length > 0) {
                    $results['second_prize'] = trim($secondNodes->item(0)->textContent);
                }
                
                // Lấy giải ba (G3)
                $thirdNodes = $xpath->query(".//tr[td[text()='G3']]/td[" . ($provinceIndex + 2) . "]//div[starts-with(@class, 'v-g3-')]", $table);
                foreach ($thirdNodes as $node) {
                    $number = trim($node->textContent);
                    if ($number) {
                        $results['third_prize'][] = $number;
                    }
                }
                
                // Lấy giải tư (G4)
                $fourthNodes = $xpath->query(".//tr[td[text()='G4']]/td[" . ($provinceIndex + 2) . "]//div[starts-with(@class, 'v-g4-')]", $table);
                foreach ($fourthNodes as $node) {
                    $number = trim($node->textContent);
                    if ($number) {
                        $results['fourth_prize'][] = $number;
                    }
                }
                
                // Lấy giải năm (G5)
                $fifthNodes = $xpath->query(".//tr[td[text()='G5']]/td[" . ($provinceIndex + 2) . "]//div[contains(@class, 'v-g5')]", $table);
                if ($fifthNodes->length > 0) {
                    $results['fifth_prize'] = trim($fifthNodes->item(0)->textContent);
                }
                
                // Lấy giải sáu (G6)
                $sixthNodes = $xpath->query(".//tr[td[text()='G6']]/td[" . ($provinceIndex + 2) . "]//div[starts-with(@class, 'v-g6-')]", $table);
                foreach ($sixthNodes as $node) {
                    $number = trim($node->textContent);
                    if ($number) {
                        $results['sixth_prize'][] = $number;
                    }
                }
                
                // Lấy giải bảy (G7)
                $seventhNodes = $xpath->query(".//tr[td[text()='G7']]/td[" . ($provinceIndex + 2) . "]//div[contains(@class, 'v-g7')]", $table);
                if ($seventhNodes->length > 0) {
                    $results['seventh_prize'] = trim($seventhNodes->item(0)->textContent);
                }
                
                // Lấy giải tám (G8)
                $eighthNodes = $xpath->query(".//tr[contains(@class, 'g8')]/td[" . ($provinceIndex + 2) . "]//div[contains(@class, 'v-g8')]", $table);
                if ($eighthNodes->length > 0) {
                    $results['eighth_prize'] = trim($eighthNodes->item(0)->textContent);
                }
            }
        } else if ($table) {
            // Nếu không có tỉnh cụ thể, lấy kết quả từ cột đầu tiên
            // Lấy giải đặc biệt (ĐB)
            $specialNodes = $xpath->query(".//tr[contains(@class, 'gdb')]/td[2]//div[contains(@class, 'v-gdb')]", $table);
            if ($specialNodes->length > 0) {
                $results['special_prize'] = trim($specialNodes->item(0)->textContent);
            }
            
            // Lấy giải nhất (G1)
            $firstNodes = $xpath->query(".//tr[td[text()='G1']]/td[2]//div[contains(@class, 'v-g1')]", $table);
            if ($firstNodes->length > 0) {
                $results['first_prize'] = trim($firstNodes->item(0)->textContent);
            }
            
            // Lấy giải nhì (G2)
            $secondNodes = $xpath->query(".//tr[td[text()='G2']]/td[2]//div[contains(@class, 'v-g2')]", $table);
            if ($secondNodes->length > 0) {
                $results['second_prize'] = trim($secondNodes->item(0)->textContent);
            }
            
            // Lấy giải ba (G3)
            $thirdNodes = $xpath->query(".//tr[td[text()='G3']]/td[2]//div[starts-with(@class, 'v-g3-')]", $table);
            foreach ($thirdNodes as $node) {
                $number = trim($node->textContent);
                if ($number) {
                    $results['third_prize'][] = $number;
                }
            }
            
            // Lấy giải tư (G4)
            $fourthNodes = $xpath->query(".//tr[td[text()='G4']]/td[2]//div[starts-with(@class, 'v-g4-')]", $table);
            foreach ($fourthNodes as $node) {
                $number = trim($node->textContent);
                if ($number) {
                    $results['fourth_prize'][] = $number;
                }
            }
            
            // Lấy giải năm (G5)
            $fifthNodes = $xpath->query(".//tr[td[text()='G5']]/td[2]//div[contains(@class, 'v-g5')]", $table);
            if ($fifthNodes->length > 0) {
                $results['fifth_prize'] = trim($fifthNodes->item(0)->textContent);
            }
            
            // Lấy giải sáu (G6)
            $sixthNodes = $xpath->query(".//tr[td[text()='G6']]/td[2]//div[starts-with(@class, 'v-g6-')]", $table);
            foreach ($sixthNodes as $node) {
                $number = trim($node->textContent);
                if ($number) {
                    $results['sixth_prize'][] = $number;
                }
            }
            
            // Lấy giải bảy (G7)
            $seventhNodes = $xpath->query(".//tr[td[text()='G7']]/td[2]//div[contains(@class, 'v-g7')]", $table);
            if ($seventhNodes->length > 0) {
                $results['seventh_prize'] = trim($seventhNodes->item(0)->textContent);
            }
            
            // Lấy giải tám (G8)
            $eighthNodes = $xpath->query(".//tr[contains(@class, 'g8')]/td[2]//div[contains(@class, 'v-g8')]", $table);
            if ($eighthNodes->length > 0) {
                $results['eighth_prize'] = trim($eighthNodes->item(0)->textContent);
            }
        }
        
        // Làm sạch kết quả, loại bỏ các ký tự không phải số
        foreach ($results as $key => $value) {
            if (is_array($value)) {
                foreach ($value as $k => $v) {
                    $results[$key][$k] = preg_replace('/[^0-9]/', '', $v);
                }
            } else {
                $results[$key] = preg_replace('/[^0-9]/', '', $value);
            }
        }
        
        return $results;
    }
    
    /**
     * Lấy tên tỉnh từ mã tỉnh và region_id
     */
    private function getProvinceNameByCode($code, $regionId)
    {
        $province = \App\Models\Province::where('code', $code)
            ->where('region_id', $regionId)
            ->first();
            
        return $province ? $province->name : $code;
    }
    
    /**
     * Kiểm tra xem kết quả có hợp lệ không
     */
    private function validateResults($results, $region)
    {
        if (!is_array($results)) {
            return false;
        }
        
        // Kiểm tra các giải quan trọng
        if (empty($results['special_prize']) || empty($results['first_prize'])) {
            return false;
        }
        
        // Kiểm tra cấu trúc dựa trên khu vực
        if ($region === 'mb') {
            // Miền Bắc
            return isset($results['second_prize']) && !empty($results['second_prize']) && 
                   isset($results['third_prize']) && !empty($results['third_prize']) && 
                   isset($results['fourth_prize']) && !empty($results['fourth_prize']) && 
                   isset($results['fifth_prize']) && !empty($results['fifth_prize']) && 
                   isset($results['sixth_prize']) && !empty($results['sixth_prize']) && 
                   isset($results['seventh_prize']) && !empty($results['seventh_prize']);
        } else {
            // Miền Nam và Miền Trung
            return isset($results['second_prize']) && 
                   isset($results['third_prize']) && !empty($results['third_prize']) && 
                   isset($results['fourth_prize']) && !empty($results['fourth_prize']) && 
                   isset($results['fifth_prize']) && 
                   isset($results['sixth_prize']) && !empty($results['sixth_prize']) && 
                   isset($results['seventh_prize']) && 
                   isset($results['eighth_prize']);
        }
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