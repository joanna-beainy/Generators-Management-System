<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Client;
use App\Models\MeterReading;
use App\Models\Maintenance;
use Illuminate\Support\Carbon;

class GenerateMonthlyMeterReadings extends Command
{
    protected $signature = 'meter:generate-readings';
    protected $description = 'Automatically create monthly meter readings for all active clients on the 28th of each month.';

    public function handle()
    {
        $now = Carbon::now();
        $targetMonth = $now->startOfMonth(); // e.g. October 1st

        $this->info("📅 Generating meter readings for {$targetMonth->format('F Y')}...");

        // Fetch all active clients
        $clients = Client::where('is_active', true)->get();

        foreach ($clients as $client) {
            // Skip if a reading already exists for this month
            $alreadyExists = MeterReading::where('client_id', $client->id)
                ->whereDate('reading_for_month', $targetMonth)
                ->exists();

            if ($alreadyExists) {
                $this->line("⏭️ Skipped client #{$client->id} — already has reading for {$targetMonth->format('F')}.");
                continue;
            }

            $lastReading = MeterReading::latestForClient($client->id);

            // Fallback values
            $previousMeter = $lastReading?->current_meter ?? $client->initial_meter ?? 0;
            $previousBalance = $lastReading?->remaining_amount ?? 0;

            // Include all maintenance costs for this month created *before* the 28th
            $maintenanceCosts = Maintenance::forClient($client->id)
                ->forMonth($targetMonth)
                ->whereRaw('DAY(created_at) < 28')
                ->sum('amount');

            // Create the new meter reading
            MeterReading::create([
                'client_id' => $client->id,
                'previous_meter' => $previousMeter,
                'current_meter' => $previousMeter, // Starts equal to previous
                'amount' => 0,
                'maintenance_cost' => $maintenanceCosts?? 0,
                'previous_balance' => $previousBalance,
                'remaining_amount' => $previousBalance + $maintenanceCosts,
                'reading_date' => null,
                'reading_for_month' => $targetMonth,
            ]);

            $this->line("✅ Created reading for client #{$client->id} — Maintenance: {$maintenanceCosts}, Prev Meter: {$previousMeter}, Prev Balance: {$previousBalance}");
        }

        $this->info('🎯 Monthly meter readings generated successfully.');
    }
}
