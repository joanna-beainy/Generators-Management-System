<?php

namespace App\Models;

use App\Support\ArabicMonth;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Maintenance extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_id',
        'applied_meter_reading_id',
        'amount',
        'description',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function appliedMeterReading()
    {
        return $this->belongsTo(MeterReading::class, 'applied_meter_reading_id');
    }

    public function scopeForMonth($query, Carbon $month)
    {
        return $query->whereYear('created_at', $month->year)
            ->whereMonth('created_at', $month->month);
    }

    public function scopeForClient($query, $clientId)
    {
        return $query->where('client_id', $clientId);
    }

    public static function addWithAutoHandling($clientId, $amount, $description = null)
    {
        $now = Carbon::now();
        $targetMonth = $now->copy()->startOfMonth();
        $latestCompletedReading = MeterReading::latestCompletedForClient($clientId);

        $maintenance = self::create([
            'client_id' => $clientId,
            'applied_meter_reading_id' => $latestCompletedReading?->id,
            'amount' => $amount,
            'description' => $description,
        ]);

        if ($latestCompletedReading) {
            $latestCompletedReading->increment('maintenance_cost', $amount);
            $latestCompletedReading->increment('remaining_amount', $amount);

            return $maintenance;
        }

        $reading = MeterReading::where('client_id', $clientId)
            ->whereDate('reading_for_month', $targetMonth)
            ->first();

        if ($reading) {
            $reading->increment('maintenance_cost', $amount);
            $reading->increment('remaining_amount', $amount);
            $maintenance->update([
                'applied_meter_reading_id' => $reading->id,
            ]);
        }

        return $maintenance;
    }

    public function deleteWithAutoHandling()
    {
        $targetMonth = $this->created_at->copy()->startOfMonth();
        $reading = $this->appliedMeterReading;

        if (!$reading) {
            $reading = MeterReading::where('client_id', $this->client_id)
                ->whereDate('reading_for_month', $targetMonth)
                ->first();
        }

        if ($reading) {
            $reading->decrement('maintenance_cost', $this->amount);
            $reading->decrement('remaining_amount', $this->amount);
        }

        return $this->delete();
    }

    public function getArabicMonthNameAttribute(): string
    {
        return ArabicMonth::label($this->created_at);
    }
}
