<?php

namespace App\Console\Commands;

use App\Models\Client;
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

            MeterReading::createPendingReadingForClientAndMonth($client, $targetMonth);

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
