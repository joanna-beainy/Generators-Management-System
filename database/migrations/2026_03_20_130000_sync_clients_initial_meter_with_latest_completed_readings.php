<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("
            UPDATE clients
            SET initial_meter = (
                SELECT mr.current_meter
                FROM meter_readings mr
                WHERE mr.client_id = clients.id
                  AND mr.reading_date IS NOT NULL
                ORDER BY mr.reading_for_month DESC
                LIMIT 1
            )
            WHERE EXISTS (
                SELECT 1
                FROM meter_readings mr
                WHERE mr.client_id = clients.id
                  AND mr.reading_date IS NOT NULL
            )
        ");
    }

    public function down(): void
    {
        // Intentionally left empty because previous initial meter values cannot be reconstructed reliably.
    }
};
