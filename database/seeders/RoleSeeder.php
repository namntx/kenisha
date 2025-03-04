<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;


class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('roles')->insert([
            ['name' => 'Admin', 'description' => 'Quản trị viên hệ thống'],
            ['name' => 'Agent', 'description' => 'Nhà cái'],
            ['name' => 'Customer', 'description' => 'Khách hàng đánh đề']
        ]);
    }
}
