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
        Schema::table('clients', function (Blueprint $table) {
            // ✅ Make these fields nullable
            $table->string('father_name')->nullable()->change();
            $table->string('last_name')->nullable()->change();
            $table->string('phone_number')->nullable()->change();

            // ✅ Add is_offered column
            $table->boolean('is_offered')->default(false)->after('user_id');

            // ✅ Make meter_category_id nullable
            $table->foreignId('meter_category_id')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            // Rollback changes
            $table->string('father_name')->nullable(false)->change();
            $table->string('last_name')->nullable(false)->change();
            $table->string('phone_number')->nullable(false)->change();
            $table->dropColumn('is_offered');
            $table->foreignId('meter_category_id')->nullable(false)->change();
        });
    }
};
