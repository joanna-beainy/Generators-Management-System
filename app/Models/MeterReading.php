<?php

namespace App\Models;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class MeterReading extends Model
{
    use HasFactory;

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
    public static function latestForClient($clientId)
    {
        return self::where('client_id', $clientId)
            ->orderByDesc('reading_for_month')
            ->first();
    }

    public static function latestCompletedForClient($clientId)
    {
        return self::where('client_id', $clientId)
            ->whereNotNull('reading_date') // Only completed readings
            ->orderByDesc('reading_for_month')
            ->first();
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