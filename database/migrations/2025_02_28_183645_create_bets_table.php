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
        Schema::create('bets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained();
            $table->foreignId('region_id')->constrained();
            $table->foreignId('bet_type_id')->constrained();
            $table->foreignId('province_id')->nullable()->constrained()->after('region_id');
            $table->date('bet_date');
            $table->string('numbers');
            $table->decimal('amount', 10, 2);
            $table->decimal('potential_win', 10, 2);
            $table->boolean('is_won')->nullable();
            $table->decimal('win_amount', 10, 2)->nullable();
            $table->boolean('is_processed')->default(false);
            $table->string('raw_input')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bets');
    }
};
