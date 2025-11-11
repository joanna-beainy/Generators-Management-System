<?php

namespace App\Console\Commands;

use App\Models\Client;
use App\Models\Maintenance;
use App\Models\MeterReading;
use Illuminate\Support\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Auth;

class GenerateMonthlyMeterReadings extends Command
{
    protected $signature = 'meter:generate-readings';
    protected $description = 'Automatically create monthly meter readings for all active clients on the 28th of each month.';

    public function handle()
    {
        $now = Carbon::now();
        $targetMonth = $now->startOfMonth();

        $this->info("📅 Generating meter readings for {$targetMonth->format('F Y')}...");
 
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

            // Get the previous meter reading
            $previousMeter = $lastReading?->current_meter ?? $client->initial_meter ?? 0;

            // For offered clients, set everything to 0
            if ($client->is_offered) {
                $amount = 0;
                $maintenanceCosts = 0;
                $previousBalance = 0;
                $remainingAmount = 0;
            } else {
                $amount = 0; // Will be calculated when meter is read
                $maintenanceCosts = Maintenance::forClient($client->id)
                    ->forMonth($targetMonth)
                    ->whereRaw('DAY(created_at) < 28')
                    ->sum('amount');
                $previousBalance = 0; // TEMPORARY - will be calculated when meter is read
                $remainingAmount = $maintenanceCosts; // Only maintenance for now
            }

            // Create the new meter reading
            MeterReading::create([
                'client_id' => $client->id,
                'previous_meter' => $previousMeter,
                'current_meter' => $previousMeter, // Starts equal to previous
                'amount' => $amount,
                'maintenance_cost' => $maintenanceCosts,
                'previous_balance' => $previousBalance,
                'remaining_amount' => $remainingAmount,
                'reading_date' => null,
                'reading_for_month' => $targetMonth,
            ]);

            
            $this->line("✅ Created meter reading for client #{$client->id} for {$targetMonth->format('F')}.");
        }

        $this->info('🎯 Monthly meter readings generated successfully.');
    }
}