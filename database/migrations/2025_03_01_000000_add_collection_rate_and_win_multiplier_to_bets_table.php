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
            $table->decimal('collection_rate', 10, 4)->nullable()->after('amount');
            $table->decimal('win_multiplier', 10, 2)->nullable()->after('collection_rate');
            $table->decimal('collected_amount', 10, 2)->nullable()->after('win_multiplier');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bets', function (Blueprint $table) {
            $table->dropColumn('collection_rate');
            $table->dropColumn('win_multiplier');
            $table->dropColumn('collected_amount');
        });
    }
};
