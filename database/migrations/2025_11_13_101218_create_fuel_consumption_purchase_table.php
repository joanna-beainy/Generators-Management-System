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
        Schema::create('fuel_consumption_purchase', function (Blueprint $table) {
            $table->id();
            $table->foreignId('fuel_consumption_id')->constrained()->onDelete('cascade');
            $table->foreignId('fuel_purchase_id')->constrained()->onDelete('cascade');
            $table->integer('liters_deducted')->unsigned();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fuel_consumption_purchase');
    }
};
