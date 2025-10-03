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
            $table->decimal('previous_meter', 10, 2)->default(0);
            $table->decimal('current_meter', 10, 2)->default(0);
            $table->decimal('amount', 10, 2)->default(0); // base monthly bill
            $table->decimal('remaining_amount', 10, 2)->default(0); // unpaid balance for that month
            $table->decimal('maintenance_cost', 10, 2)->default(0); // monthly maintenance
            $table->date('reading_date')->nullable(); // set to now() when reading entered
            $table->date('reading_for_month'); // month this reading belongs to
            $table->enum('status', ['unpaid', 'pending', 'paid'])->default('unpaid');
            $table->softDeletes(); // ✅ enable soft deletes
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
