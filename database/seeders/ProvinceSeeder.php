<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProvinceSeeder extends Seeder
{
    public function run()
    {
        // Miền Bắc (chỉ có 1 đài)
        DB::table('provinces')->insert([
            ['name' => 'Miền Bắc', 'code' => 'mb', 'region_id' => 1, 'draw_day' => 1, 'is_active' => true], // Thứ 2
            ['name' => 'Miền Bắc', 'code' => 'mb', 'region_id' => 1, 'draw_day' => 2, 'is_active' => true], // Thứ 3
            ['name' => 'Miền Bắc', 'code' => 'mb', 'region_id' => 1, 'draw_day' => 3, 'is_active' => true], // Thứ 4
            ['name' => 'Miền Bắc', 'code' => 'mb', 'region_id' => 1, 'draw_day' => 4, 'is_active' => true], // Thứ 5
            ['name' => 'Miền Bắc', 'code' => 'mb', 'region_id' => 1, 'draw_day' => 5, 'is_active' => true], // Thứ 6
            ['name' => 'Miền Bắc', 'code' => 'mb', 'region_id' => 1, 'draw_day' => 6, 'is_active' => true], // Thứ 7
            ['name' => 'Miền Bắc', 'code' => 'mb', 'region_id' => 1, 'draw_day' => 0, 'is_active' => true], // Chủ nhật
        ]);

        // Miền Trung
        DB::table('provinces')->insert([
            // Thứ 2
            ['name' => 'Thừa Thiên Huế', 'code' => 'tth', 'region_id' => 2, 'draw_day' => 1, 'is_active' => true],
            ['name' => 'Phú Yên', 'code' => 'py', 'region_id' => 2, 'draw_day' => 1, 'is_active' => true],
            
            // Thứ 3
            ['name' => 'Đắk Lắk', 'code' => 'dlk', 'region_id' => 2, 'draw_day' => 2, 'is_active' => true],
            ['name' => 'Quảng Nam', 'code' => 'qna', 'region_id' => 2, 'draw_day' => 2, 'is_active' => true],
            
            // Thứ 4
            ['name' => 'Đà Nẵng', 'code' => 'dng', 'region_id' => 2, 'draw_day' => 3, 'is_active' => true],
            ['name' => 'Khánh Hòa', 'code' => 'kh', 'region_id' => 2, 'draw_day' => 3, 'is_active' => true],
            
            // Thứ 5
            ['name' => 'Bình Định', 'code' => 'bdh', 'region_id' => 2, 'draw_day' => 4, 'is_active' => true],
            ['name' => 'Quảng Trị', 'code' => 'qt', 'region_id' => 2, 'draw_day' => 4, 'is_active' => true],
            ['name' => 'Quảng Bình', 'code' => 'qb', 'region_id' => 2, 'draw_day' => 4, 'is_active' => true],
            
            // Thứ 6
            ['name' => 'Gia Lai', 'code' => 'gl', 'region_id' => 2, 'draw_day' => 5, 'is_active' => true],
            ['name' => 'Ninh Thuận', 'code' => 'nt', 'region_id' => 2, 'draw_day' => 5, 'is_active' => true],
            
            // Thứ 7
            ['name' => 'Đà Nẵng', 'code' => 'dng', 'region_id' => 2, 'draw_day' => 6, 'is_active' => true],
            ['name' => 'Quảng Ngãi', 'code' => 'qng', 'region_id' => 2, 'draw_day' => 6, 'is_active' => true],
            ['name' => 'Đắk Nông', 'code' => 'dno', 'region_id' => 2, 'draw_day' => 6, 'is_active' => true],
            
            // Chủ nhật
            ['name' => 'Khánh Hòa', 'code' => 'kh', 'region_id' => 2, 'draw_day' => 0, 'is_active' => true],
            ['name' => 'Kon Tum', 'code' => 'kt', 'region_id' => 2, 'draw_day' => 0, 'is_active' => true],
        ]);

        // Miền Nam
        DB::table('provinces')->insert([
            // Thứ 2
            ['name' => 'Đồng Tháp', 'code' => 'dt', 'region_id' => 3, 'draw_day' => 1, 'is_active' => true],
            ['name' => 'Hồ Chí Minh', 'code' => 'hcm', 'region_id' => 3, 'draw_day' => 1, 'is_active' => true],
            ['name' => 'Cà Mau', 'code' => 'cm', 'region_id' => 3, 'draw_day' => 1, 'is_active' => true],
            
            // Thứ 3
            ['name' => 'Bến Tre', 'code' => 'bt', 'region_id' => 3, 'draw_day' => 2, 'is_active' => true],
            ['name' => 'Vũng Tàu', 'code' => 'vt', 'region_id' => 3, 'draw_day' => 2, 'is_active' => true],
            ['name' => 'Bạc Liêu', 'code' => 'bl', 'region_id' => 3, 'draw_day' => 2, 'is_active' => true],
            
            // Thứ 4
            ['name' => 'Đồng Nai', 'code' => 'dna', 'region_id' => 3, 'draw_day' => 3, 'is_active' => true],
            ['name' => 'Cần Thơ', 'code' => 'ct', 'region_id' => 3, 'draw_day' => 3, 'is_active' => true],
            ['name' => 'Sóc Trăng', 'code' => 'st', 'region_id' => 3, 'draw_day' => 3, 'is_active' => true],
            
            // Thứ 5
            ['name' => 'Tây Ninh', 'code' => 'tn', 'region_id' => 3, 'draw_day' => 4, 'is_active' => true],
            ['name' => 'An Giang', 'code' => 'ag', 'region_id' => 3, 'draw_day' => 4, 'is_active' => true],
            ['name' => 'Bình Thuận', 'code' => 'bth', 'region_id' => 3, 'draw_day' => 4, 'is_active' => true],
            
            // Thứ 6
            ['name' => 'Vĩnh Long', 'code' => 'vl', 'region_id' => 3, 'draw_day' => 5, 'is_active' => true],
            ['name' => 'Bình Dương', 'code' => 'bd', 'region_id' => 3, 'draw_day' => 5, 'is_active' => true],
            ['name' => 'Trà Vinh', 'code' => 'tv', 'region_id' => 3, 'draw_day' => 5, 'is_active' => true],
            
            // Thứ 7
            ['name' => 'Hồ Chí Minh', 'code' => 'hcm', 'region_id' => 3, 'draw_day' => 6, 'is_active' => true],
            ['name' => 'Long An', 'code' => 'la', 'region_id' => 3, 'draw_day' => 6, 'is_active' => true],
            ['name' => 'Hậu Giang', 'code' => 'hg', 'region_id' => 3, 'draw_day' => 6, 'is_active' => true],
            ['name' => 'Bình Phước', 'code' => 'bp', 'region_id' => 3, 'draw_day' => 6, 'is_active' => true],
            
            // Chủ nhật
            ['name' => 'Tiền Giang', 'code' => 'tg', 'region_id' => 3, 'draw_day' => 0, 'is_active' => true],
            ['name' => 'Kiên Giang', 'code' => 'kg', 'region_id' => 3, 'draw_day' => 0, 'is_active' => true],
            ['name' => 'Đà Lạt', 'code' => 'dl', 'region_id' => 3, 'draw_day' => 0, 'is_active' => true],
        ]);
    }
}