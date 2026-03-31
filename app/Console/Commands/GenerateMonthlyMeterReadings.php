<?php

namespace App\Console\Commands;

use App\Models\Client;
use App\Models\Maintenance;
use App\Models\MeterReading;
use Illuminate\Support\Carbon;
use Illuminate\Console\Command;
use Native\Desktop\Facades\Notification;

class GenerateMonthlyMeterReadings extends Command
{
    protected $signature = 'meter:generate-readings';
    protected $description = 'Automatically create monthly meter readings for all active clients on the 28th of each month.';

    public function handle()
    {
        $now = Carbon::now();
        $targetMonth = $now->startOfMonth();

        $this->info("Generating meter readings for {$targetMonth->format('F Y')}...");

        $clients = Client::where('is_active', true)->get();

        foreach ($clients as $client) {
            $alreadyExists = MeterReading::where('client_id', $client->id)
                ->whereDate('reading_for_month', $targetMonth)
                ->exists();

            if ($alreadyExists) {
                $this->line("Skipped client #{$client->id} - already has reading for {$targetMonth->format('F')}.");
                continue;
            }

            // initial_meter is the baseline used to start the next generated reading.
            $previousMeter = $client->initial_meter ?? 0;
            $pendingMaintenances = collect();

            if ($client->is_offered) {
                $amount = 0;
                $maintenanceCosts = 0;
                $previousBalance = 0;
                $remainingAmount = 0;
            } else {
                $amount = 0;
                $pendingMaintenances = Maintenance::forClient($client->id)
                    ->forMonth($targetMonth)
                    ->whereNull('applied_meter_reading_id')
                    ->whereRaw("strftime('%d', created_at) < '28'")
                    ->get();
                $maintenanceCosts = (float) $pendingMaintenances->sum('amount');
                $previousBalance = 0;
                $remainingAmount = $maintenanceCosts;
            }

            $meterReading = MeterReading::create([
                'client_id' => $client->id,
                'previous_meter' => $previousMeter,
                'current_meter' => $previousMeter,
                'amount' => $amount,
                'maintenance_cost' => $maintenanceCosts,
                'previous_balance' => $previousBalance,
                'remaining_amount' => $remainingAmount,
                'reading_date' => null,
                'reading_for_month' => $targetMonth,
            ]);

            if ($pendingMaintenances->isNotEmpty()) {
                Maintenance::whereIn('id', $pendingMaintenances->pluck('id'))
                    ->update(['applied_meter_reading_id' => $meterReading->id]);
            }

            $this->line("Created meter reading for client #{$client->id} for {$targetMonth->format('F')}.");
        }

        $this->info('Monthly meter readings generated successfully.');

        try {
            Notification::new()
                ->title('Readings Generated')
                ->message('Monthly meter readings for all active clients have been generated.')
                ->show();
        } catch (\Exception $e) {
            // Ignore notification failures
        }
    }
}
