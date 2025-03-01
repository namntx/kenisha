<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BetTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('bet_types')->insert([
            // Lô đề cơ bản
            [
                'name' => 'Đề',
                'code' => 'de',
                'description' => '2 số cuối của giải đặc biệt',
                'syntax_pattern' => 'de \d{2}( \d{2})* \d+',
                'example' => 'MB de 23 45 10000',
                'payout_ratio' => 70.00,
                'is_active' => true
            ],
            [
                'name' => 'Lô',
                'code' => 'lo',
                'description' => '2 số cuối xuất hiện ở bất kỳ giải nào',
                'syntax_pattern' => 'lo \d{2}( \d{2})* \d+',
                'example' => 'MB lo 23 45 10000',
                'payout_ratio' => 3.50,
                'is_active' => true
            ],
            [
                'name' => '3 Càng',
                'code' => '3c',
                'description' => '3 số cuối của giải đặc biệt',
                'syntax_pattern' => '3c \d{3}( \d{3})* \d+',
                'example' => 'MB 3c 234 567 10000',
                'payout_ratio' => 700.00,
                'is_active' => true
            ],
            [
                'name' => '4 Càng',
                'code' => '4c',
                'description' => '4 số cuối của giải đặc biệt',
                'syntax_pattern' => '4c \d{4}( \d{4})* \d+',
                'example' => 'MB 4c 1234 5678 10000',
                'payout_ratio' => 7000.00,
                'is_active' => true
            ],
            
            // Đầu/Đuôi
            [
                'name' => 'Đầu',
                'code' => 'dau',
                'description' => 'Chữ số đầu tiên của giải đặc biệt',
                'syntax_pattern' => 'dau \d( \d)* \d+',
                'example' => 'MB dau 2 5 10000',
                'payout_ratio' => 9.00,
                'is_active' => true
            ],
            [
                'name' => 'Đuôi',
                'code' => 'duoi',
                'description' => 'Chữ số cuối cùng của giải đặc biệt',
                'syntax_pattern' => 'duoi \d( \d)* \d+',
                'example' => 'MB duoi 3 7 10000',
                'payout_ratio' => 9.00,
                'is_active' => true
            ],
            
            // Xiên
            [
                'name' => 'Xiên 2',
                'code' => 'xien2',
                'description' => 'Xiên 2 số (Miền Bắc)',
                'syntax_pattern' => 'xien2 \d{2}( \d{2})* \d+',
                'example' => 'MB xien2 23 45 10000',
                'payout_ratio' => 12.00,
                'is_active' => true
            ],
            [
                'name' => 'Xiên 3',
                'code' => 'xien3',
                'description' => 'Xiên 3 số (Miền Bắc)',
                'syntax_pattern' => 'xien3 \d{2}( \d{2})* \d+',
                'example' => 'MB xien3 23 45 67 10000',
                'payout_ratio' => 40.00,
                'is_active' => true
            ],
            [
                'name' => 'Xiên 4',
                'code' => 'xien4',
                'description' => 'Xiên 4 số (Miền Bắc)',
                'syntax_pattern' => 'xien4 \d{2}( \d{2})* \d+',
                'example' => 'MB xien4 23 45 67 89 10000',
                'payout_ratio' => 100.00,
                'is_active' => true
            ],
            [
                'name' => 'Xiên 5',
                'code' => 'xien5',
                'description' => 'Xiên 5 số (Miền Bắc)',
                'syntax_pattern' => 'xien5 \d{2}( \d{2})* \d+',
                'example' => 'MB xien5 23 45 67 89 12 10000',
                'payout_ratio' => 200.00,
                'is_active' => true
            ],
            [
                'name' => 'Xiên 6',
                'code' => 'xien6',
                'description' => 'Xiên 6 số (Miền Bắc)',
                'syntax_pattern' => 'xien6 \d{2}( \d{2})* \d+',
                'example' => 'MB xien6 23 45 67 89 12 34 10000',
                'payout_ratio' => 400.00,
                'is_active' => true
            ],
            
            // Đá thẳng và đá xiên (Miền Nam, Trung)
            [
                'name' => 'Đá Thẳng',
                'code' => 'da_thang',
                'description' => 'Đá thẳng (Miền Nam, Trung)',
                'syntax_pattern' => 'da_thang \d{2}( \d{2})* \d+',
                'example' => 'MN da_thang 23 45 10000',
                'payout_ratio' => 70.00,
                'is_active' => true
            ],
            [
                'name' => 'Đá Xiên',
                'code' => 'da_xien',
                'description' => 'Đá xiên (Miền Nam, Trung)',
                'syntax_pattern' => 'da_xien \d{2}( \d{2})* \d+',
                'example' => 'MN da_xien 23 45 10000',
                'payout_ratio' => 10.00,
                'is_active' => true
            ],
            
            // Đá (Miền Bắc)
            [
                'name' => 'Đá',
                'code' => 'da',
                'description' => 'Đá (Miền Bắc)',
                'syntax_pattern' => 'da \d{2}( \d{2})* \d+',
                'example' => 'MB da 23 45 10000',
                'payout_ratio' => 10.00,
                'is_active' => true
            ],
            
            // Additional bet types
            [
                'name' => 'Đầu Đuôi',
                'code' => 'dau_duoi',
                'description' => 'Đánh cả đầu và đuôi của giải đặc biệt',
                'syntax_pattern' => 'dau_duoi \d( \d)* \d+',
                'example' => 'MB dau_duoi 2 5 10000',
                'payout_ratio' => 4.50,
                'is_active' => true
            ],
            [
                'name' => 'Bao Lô',
                'code' => 'bao_lo',
                'description' => 'Đánh bao lô (tất cả các vị trí)',
                'syntax_pattern' => 'bao_lo \d{2}( \d{2})* \d+',
                'example' => 'MB bao_lo 23 45 10000',
                'payout_ratio' => 3.00,
                'is_active' => true
            ],
            [
                'name' => 'Xỉu Chủ',
                'code' => 'xiu_chu',
                'description' => 'Đánh xỉu chủ',
                'syntax_pattern' => 'xiu_chu \d{2}( \d{2})* \d+',
                'example' => 'MB xiu_chu 23 45 10000',
                'payout_ratio' => 3.50,
                'is_active' => true
            ],
            [
                'name' => 'Bảy Lô',
                'code' => 'bay_lo',
                'description' => 'Đánh bảy lô',
                'syntax_pattern' => 'bay_lo \d{2}( \d{2})* \d+',
                'example' => 'MB bay_lo 23 45 10000',
                'payout_ratio' => 7.00,
                'is_active' => true
            ],
            [
                'name' => 'Tám Lô',
                'code' => 'tam_lo',
                'description' => 'Đánh tám lô',
                'syntax_pattern' => 'tam_lo \d{2}( \d{2})* \d+',
                'example' => 'MB tam_lo 23 45 10000',
                'payout_ratio' => 8.00,
                'is_active' => true
            ],
        ]);
    }
}
