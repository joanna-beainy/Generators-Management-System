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
        Schema::table('meter_readings', function (Blueprint $table) {
            $table->dropColumn('status');
            $table->decimal('previous_balance', 10, 2)->default(0)->after('maintenance_cost');
            $table->decimal('remaining_amount', 10, 2)->default(0)->after('previous_balance')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('meter_readings', function (Blueprint $table) {
            $table->string('status')->default('unpaid')->after('reading_for_month');
            $table->dropColumn('previous_balance');
        });
    }
};
