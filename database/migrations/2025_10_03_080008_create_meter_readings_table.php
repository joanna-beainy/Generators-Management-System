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
        Schema::create('meter_readings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained()->onDelete('cascade');
            $table->unsignedInteger('previous_meter')->default(0);
            $table->unsignedInteger('current_meter')->default(0);
            $table->decimal('amount', 10, 2)->default(0);
            $table->decimal('remaining_amount', 10, 2)->default(0); 
            $table->decimal('maintenance_cost', 10, 2)->default(0);
            $table->date('reading_date')->nullable();
            $table->date('reading_for_month');
            $table->enum('status', ['unpaid', 'pending', 'paid'])->default('unpaid');
            $table->softDeletes(); 
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('meter_readings');
    }
};
