<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('bet_types', function (Blueprint $table) {
            $table->id();
            $table->string('code', 10)->unique()->comment('Mã loại cược (de, lo, 3c, 4c, ...)');
            $table->string('name')->comment('Tên loại cược (Đề, Lô, 3 càng, 4 càng, ...)');
            $table->decimal('payout_ratio', 8, 2)->nullable();
            $table->text('description')->nullable()->comment('Mô tả chi tiết về loại cược');
            $table->string('syntax_pattern')->nullable()->comment('Mẫu cú pháp regex để nhận dạng loại cược');
            $table->string('example')->nullable()->comment('Ví dụ về cú pháp cược');
            $table->boolean('is_active')->default(true)->comment('Trạng thái kích hoạt');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bet_types');
    }
};
