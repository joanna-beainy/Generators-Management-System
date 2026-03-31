<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('maintenances', function (Blueprint $table) {
            $table->foreignId('applied_meter_reading_id')
                ->nullable()
                ->after('client_id')
                ->constrained('meter_readings')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('maintenances', function (Blueprint $table) {
            $table->dropConstrainedForeignId('applied_meter_reading_id');
        });
    }
};
