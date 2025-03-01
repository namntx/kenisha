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
        Schema::table('bets', function (Blueprint $table) {
            $table->string('bet_syntax')->nullable()->after('raw_input')->comment('Cú pháp cược đã được phân tích');
            $table->json('parsed_data')->nullable()->after('bet_syntax')->comment('Dữ liệu đã được phân tích từ cú pháp cược');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bets', function (Blueprint $table) {
            $table->dropColumn('bet_syntax');
            $table->dropColumn('parsed_data');
        });
    }
};
