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
             ['name' => 'Đề', 'code' => 'de', 'description' => '2 số cuối của giải đặc biệt'],
             ['name' => 'Lô', 'code' => 'lo', 'description' => '2 số cuối xuất hiện ở bất kỳ giải nào'],
             ['name' => '3 Càng', 'code' => '3c', 'description' => '3 số cuối của giải đặc biệt'],
             ['name' => '4 Càng', 'code' => '4c', 'description' => '4 số cuối của giải đặc biệt'],
             
             // Đầu/Đuôi
             ['name' => 'Đầu', 'code' => 'dau', 'description' => 'Chữ số đầu tiên của giải đặc biệt'],
             ['name' => 'Đuôi', 'code' => 'duoi', 'description' => 'Chữ số cuối cùng của giải đặc biệt'],
             
             // Xiên
             ['name' => 'Xiên 2', 'code' => 'xien2', 'description' => 'Xiên 2 số (Miền Bắc)'],
             ['name' => 'Xiên 3', 'code' => 'xien3', 'description' => 'Xiên 3 số (Miền Bắc)'],
             ['name' => 'Xiên 4', 'code' => 'xien4', 'description' => 'Xiên 4 số (Miền Bắc)'],
             ['name' => 'Xiên 5', 'code' => 'xien5', 'description' => 'Xiên 5 số (Miền Bắc)'],
             ['name' => 'Xiên 6', 'code' => 'xien6', 'description' => 'Xiên 6 số (Miền Bắc)'],
             
             // Đá thẳng và đá xiên (Miền Nam, Trung)
             ['name' => 'Đá Thẳng', 'code' => 'da_thang', 'description' => 'Đá thẳng (Miền Nam, Trung)'],
             ['name' => 'Đá Xiên', 'code' => 'da_xien', 'description' => 'Đá xiên (Miền Nam, Trung)'],
             
             // Đá (Miền Bắc)
             ['name' => 'Đá', 'code' => 'da', 'description' => 'Đá (Miền Bắc)'],
        ]);
    }
}
