<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RegionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('regions')->insert([
            ['name' => 'Miền Bắc', 'code' => 'mb', 'description' => 'Xổ số miền Bắc'],
            ['name' => 'Miền Trung', 'code' => 'mt', 'description' => 'Xổ số miền Trung'],
            ['name' => 'Miền Nam', 'code' => 'mn', 'description' => 'Xổ số miền Nam']
        ]);
    }
}
