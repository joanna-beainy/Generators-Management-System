<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class MeterReading extends Model
{
    use HasFactory;

    protected static function booted(): void
    {
        static::saving(function (MeterReading $meterReading) {
            if ((int) $meterReading->current_meter < (int) $meterReading->previous_meter) {
                throw new \InvalidArgumentException('العداد الحالي يجب أن يكون أكبر من أو يساوي العداد السابق.');
            }
        });
    }

    protected $fillable = [
        'client_id',
        'previous_meter',
        'current_meter',
        'amount',
        'maintenance_cost',
        'previous_balance',
        'remaining_amount',
        'reading_date',
        'reading_for_month',
    ];

    protected $casts = [
        'reading_date' => 'date',
        'reading_for_month' => 'date',
        'amount' => 'decimal:2',
        'maintenance_cost' => 'decimal:2',
        'previous_balance' => 'decimal:2',
        'remaining_amount' => 'decimal:2',
        'previous_meter' => 'integer',
        'current_meter' => 'integer',
    ];

    protected $appends = ['consumption', 'total_due', 'is_completed'];

    // Relationships
    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    // Accessors
    public function getConsumptionAttribute(): int
    {
        return $this->current_meter - $this->previous_meter;
    }

    public function getTotalDueAttribute(): float
    {
        return $this->amount + $this->maintenance_cost + $this->previous_balance;
    }

    public function getIsCompletedAttribute(): bool
    {
        return !is_null($this->reading_date);
    }

    public function getIsPendingAttribute(): bool
    {
        return is_null($this->reading_date);
    }

    // Static helpers
    public static function latestCompletedForClient($clientId)
    {
        return self::where('client_id', $clientId)
            ->whereNotNull('reading_date') // Only completed readings
            ->orderByDesc('reading_for_month')
            ->first();
    }

    public static function actualPreviousBalanceForClientBeforeMonth(int $clientId, $currentMonth): float
    {
        $previousReading = self::where('client_id', $clientId)
            ->beforeMonth($currentMonth)
            ->completed()
            ->latest()
            ->first();

        return (float) ($previousReading?->remaining_amount ?? 0);
    }

    public static function syncLatestPendingBaselineForClient(int $clientId, int $baselineMeter): void
    {
        $latestPendingReading = self::where('client_id', $clientId)
            ->pending()
            ->latest('reading_for_month')
            ->first();

        if (!$latestPendingReading) {
            return;
        }

        if (
            (int) $latestPendingReading->previous_meter === $baselineMeter &&
            (int) $latestPendingReading->current_meter === $baselineMeter
        ) {
            return;
        }

        $latestPendingReading->update([
            'previous_meter' => $baselineMeter,
            'current_meter' => $baselineMeter,
        ]);
    }

    public static function deleteLatestPendingReadingForClient(int $clientId): bool
    {
        $pendingReading = self::where('client_id', $clientId)
            ->pending()
            ->latest('reading_for_month')
            ->first();

        if (!$pendingReading || $pendingReading->hasPayments()) {
            return false;
        }

        DB::transaction(function () use ($pendingReading) {
            Maintenance::where('applied_meter_reading_id', $pendingReading->id)
                ->update(['applied_meter_reading_id' => null]);

            $pendingReading->delete();
        });

        return true;
    }

    public static function latestPendingMonthForUser(int $userId): ?Carbon
    {
        $latestDate = self::pending()
            ->whereHas('client', function ($query) use ($userId) {
                $query->where('user_id', $userId);
            })
            ->max('reading_for_month');

        return $latestDate ? Carbon::parse($latestDate)->startOfMonth() : null;
    }

    public static function createPendingReadingForClientAndMonth(Client $client, Carbon $targetMonth): self
    {
        $existingReading = self::where('client_id', $client->id)
            ->whereDate('reading_for_month', $targetMonth)
            ->first();

        if ($existingReading) {
            return $existingReading;
        }

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
                ->whereDay('created_at', '<', 28)
                ->get();
            $maintenanceCosts = (float) $pendingMaintenances->sum('amount');
            $previousBalance = 0;
            $remainingAmount = $maintenanceCosts;
        }

        return DB::transaction(function () use (
            $client,
            $targetMonth,
            $previousMeter,
            $amount,
            $maintenanceCosts,
            $previousBalance,
            $remainingAmount,
            $pendingMaintenances
        ) {
            $meterReading = self::create([
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

            return $meterReading;
        });
    }

    public static function latestMonthReadings(int $userId)
    {
        // 1. Find the latest reading_for_month for this user's clients
        $latestDate = self::whereHas('client', function ($q) use ($userId) {
            $q->where('user_id', $userId);
        })->max('reading_for_month');

        if (!$latestDate) {
            return collect();
        }

        // 2. Get all readings for that month belonging to this user's clients
        return self::whereHas('client', function ($q) use ($userId) {
                $q->where('user_id', $userId);
            })
            ->where('reading_for_month', $latestDate)
            ->get()
            ->sortBy('client_id')
            ->values();
    }

    // Scopes
    public function scopeForClient($query, $clientId)
    {
        return $query->where('client_id', $clientId);
    }

    public function scopeCompleted($query)
    {
        return $query->whereNotNull('reading_date');
    }

    public function scopePending($query)
    {
        return $query->whereNull('reading_date');
    }

    public function scopeLatest($query)
    {
        return $query->orderByDesc('reading_for_month');
    }

    public function scopeForMonth($query, $month)
    {
        return $query->where('reading_for_month', $month);
    }

    public function scopeBeforeMonth($query, $month)
    {
        return $query->where('reading_for_month', '<', $month);
    }

    public function hasPayments(): bool
    {
        return $this->payments()->exists();
    }

    public function totalPaid(): float
    {
        return (float) $this->payments()->sum(DB::raw('amount + discount'));
    }

    public function hasCredit(): bool
    {
        return $this->remaining_amount < 0;
    }

    public function getCreditAmount(): float
    {
        return $this->hasCredit() ? abs($this->remaining_amount) : 0;
    }
}
