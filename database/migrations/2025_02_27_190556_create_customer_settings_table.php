<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCustomerSettingsTable extends Migration
{
    public function up()
    {
        Schema::create('customer_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            
            // Cài đặt chung
            $table->boolean('is_sync_enabled')->default(false)->comment('Chạy số (Chủ/Khách)');
            $table->decimal('cashback_all', 5, 2)->default(0)->comment('% Hồi cả ngày');
            $table->decimal('cashback_north', 5, 2)->default(0)->comment('% Hồi miền Bắc');
            $table->decimal('cashback_central', 5, 2)->default(0)->comment('% Hồi miền Trung');
            $table->decimal('cashback_south', 5, 2)->default(0)->comment('% Hồi miền Nam');
            
            // Cài đặt miền Nam
            $table->decimal('south_head_tail_rate', 8, 3)->default(0.750)->comment('Giá cò 2 Con Đầu - Đuôi MN');
            $table->decimal('south_lo_rate', 8, 3)->default(21.900)->comment('Giá Cò 2 Con lô MN');
            $table->decimal('south_3_digits_rate', 8, 3)->default(0.750)->comment('Giá cò 3 Con MN');
            $table->decimal('south_3_head_tail_rate', 8, 3)->default(0)->comment('Giá cò Xỉu Chủ MN (3 con đầu đuôi)');
            $table->decimal('south_4_digits_rate', 8, 3)->default(0.750)->comment('Giá cò 4 Con MN');
            $table->decimal('south_slide_rate', 8, 3)->default(0.750)->comment('Giá Cò Ðá Xiên MN');
            $table->decimal('south_straight_rate', 8, 3)->default(0.750)->comment('Giá Cò Đá Thẳng MN');
            
            $table->decimal('south_head_tail_win', 8, 3)->default(75)->comment('Trúng 2 Con Đầu - Đuôi MN');
            $table->decimal('south_lo_win', 8, 3)->default(75)->comment('Trúng 2 Con lô MN');
            $table->decimal('south_3_digits_win', 8, 3)->default(650)->comment('Trúng 3 Con MN');
            $table->decimal('south_3_head_tail_win', 8, 3)->default(0)->comment('Trúng Xỉu Chủ MN (3 con đầu đuôi)');
            $table->decimal('south_4_digits_win', 8, 3)->default(5500)->comment('Trúng 4 Con MN');
            $table->decimal('south_slide_win', 8, 3)->default(550)->comment('Trúng Đá Xiên MN');
            $table->decimal('south_straight_win', 8, 3)->default(750)->comment('Trúng Đá Thẳng MN');
            
            $table->boolean('south_straight_bonus')->default(false)->comment('Thưởng Đá Thẳng MN');
            $table->tinyInteger('south_straight_win_type')->default(2)->comment('Cách trúng đá thẳng MN: 1=Một lần, 2=Ky rưỡi, 3=Nhiều cặp');
            $table->tinyInteger('south_slide_win_type')->default(3)->comment('Cách trúng đá xiên MN: 1=Một lần, 2=Ky rưỡi, 3=Nhiều cặp');
            
            // Cài đặt miền Bắc
            $table->decimal('north_head_tail_rate', 8, 3)->default(0.750)->comment('Giá cò 2 Con Đầu - Đuôi MB');
            $table->decimal('north_lo_rate', 8, 3)->default(21.900)->comment('Giá Cò 2 Con lô MB');
            $table->decimal('north_3_digits_rate', 8, 3)->default(0.750)->comment('Giá cò 3 Con MB');
            $table->decimal('north_3_head_tail_rate', 8, 3)->default(0)->comment('Giá cò Xỉu Chủ MB (3 con đầu đuôi)');
            $table->decimal('north_4_digits_rate', 8, 3)->default(0.750)->comment('Giá cò 4 Con MB');
            $table->decimal('north_slide_rate', 8, 3)->default(0.750)->comment('Giá Cò Đá MB');
            
            $table->decimal('north_head_tail_win', 8, 3)->default(75)->comment('Trúng 2 Con Đầu - Đuôi MB');
            $table->decimal('north_lo_win', 8, 3)->default(75)->comment('Trúng 2 Con lô MB');
            $table->decimal('north_3_digits_win', 8, 3)->default(650)->comment('Trúng 3 Con MB');
            $table->decimal('north_3_head_tail_win', 8, 3)->default(0)->comment('Trúng Xỉu Chủ MB (3 con đầu đuôi)');
            $table->decimal('north_4_digits_win', 8, 3)->default(5500)->comment('Trúng 4 Con MB');
            $table->decimal('north_slide_win', 8, 3)->default(650)->comment('Trúng Đá MB');
            
            $table->boolean('north_straight_bonus')->default(false)->comment('Thưởng Đá Thẳng MB');
            $table->tinyInteger('north_slide_win_type')->default(2)->comment('Cách trúng đá MB: 1=Một lần, 2=Ky rưỡi, 3=Nhiều cặp');
            
            // Xiên miền Bắc
            $table->decimal('north_slide2_rate', 8, 3)->default(0.750)->comment('Giá cò xiên 2 MB');
            $table->decimal('north_slide2_win', 8, 3)->default(75)->comment('Trúng xiên 2 MB');
            $table->decimal('north_slide3_rate', 8, 3)->default(0.750)->comment('Giá cò xiên 3 MB');
            $table->decimal('north_slide3_win', 8, 3)->default(75)->comment('Trúng xiên 3 MB');
            $table->decimal('north_slide4_rate', 8, 3)->default(0.750)->comment('Giá cò xiên 4 MB');
            $table->decimal('north_slide4_win', 8, 3)->default(75)->comment('Trúng xiên 4 MB');
            $table->decimal('north_slide5_rate', 8, 3)->default(0.750)->comment('Giá cò xiên 5 MB');
            $table->decimal('north_slide5_win', 8, 3)->default(75)->comment('Trúng xiên 5 MB');
            $table->decimal('north_slide6_rate', 8, 3)->default(0.750)->comment('Giá cò xiên 6 MB');
            $table->decimal('north_slide6_win', 8, 3)->default(75)->comment('Trúng xiên 6 MB');
            
            // Cài đặt miền Trung
            $table->decimal('central_head_tail_rate', 8, 3)->default(0.750)->comment('Giá cò 2 Con Đầu - Đuôi MT');
            $table->decimal('central_lo_rate', 8, 3)->default(21.900)->comment('Giá Cò 2 Con lô MT');
            $table->decimal('central_3_digits_rate', 8, 3)->default(0.750)->comment('Giá cò 3 Con MT');
            $table->decimal('central_3_head_tail_rate', 8, 3)->default(0)->comment('Giá cò Xỉu Chủ MT (3 con đầu đuôi)');
            $table->decimal('central_4_digits_rate', 8, 3)->default(0.750)->comment('Giá cò 4 Con MT');
            $table->decimal('central_slide_rate', 8, 3)->default(0.750)->comment('Giá Cò Ðá Xiên MT');
            $table->decimal('central_straight_rate', 8, 3)->default(0.750)->comment('Giá Cò Đá Thẳng MT');
            
            $table->decimal('central_head_tail_win', 8, 3)->default(75)->comment('Trúng 2 Con Đầu - Đuôi MT');
            $table->decimal('central_lo_win', 8, 3)->default(75)->comment('Trúng 2 Con lô MT');
            $table->decimal('central_3_digits_win', 8, 3)->default(650)->comment('Trúng 3 Con MT');
            $table->decimal('central_3_head_tail_win', 8, 3)->default(0)->comment('Trúng Xỉu Chủ MT (3 con đầu đuôi)');
            $table->decimal('central_4_digits_win', 8, 3)->default(5500)->comment('Trúng 4 Con MT');
            $table->decimal('central_slide_win', 8, 3)->default(550)->comment('Trúng Đá Xiên MT');
            $table->decimal('central_straight_win', 8, 3)->default(750)->comment('Trúng Đá Thẳng MT');
            
            $table->boolean('central_straight_bonus')->default(false)->comment('Thưởng Đá Thẳng MT');
            $table->tinyInteger('central_straight_win_type')->default(2)->comment('Cách trúng đá thẳng MT: 1=Một lần, 2=Ky rưỡi, 3=Nhiều cặp');
            $table->tinyInteger('central_slide_win_type')->default(3)->comment('Cách trúng đá xiên MT: 1=Một lần, 2=Ky rưỡi, 3=Nhiều cặp');
            
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('customer_settings');
    }
}