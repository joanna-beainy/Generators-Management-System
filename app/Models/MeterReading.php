<?php

namespace App\Models;

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
    ];

    protected $appends = ['consumption', 'total_due'];

    //Relationships
    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    //Accessors
    public function getConsumptionAttribute(): int
    {
        return $this->current_meter - $this->previous_meter;
    }

    public function getTotalDueAttribute(): float
    {
        return $this->amount + $this->maintenance_cost + $this->previous_balance;
    }

    //Static helper
    public static function latestForClient($clientId)
    {
        return self::where('client_id', $clientId)
            ->orderByDesc('reading_for_month')
            ->first();
    }

    public static function latestPerActiveClient(int $userId)
    {
        return self::whereHas('client', fn ($q) => 
                $q->active()
                ->where('user_id', $userId)
            )
            ->orderByDesc('reading_for_month')
            ->get()
            ->groupBy('client_id')
            ->map(fn ($group) => $group->first()) 
            ->sortBy('client_id')
            ->values();
    }


    //scopes
    public function scopeForClient($query, $clientId)
    {
        return $query->where('client_id', $clientId);
    }

    public function scopeLatest($query)
    {
        return $query->orderByDesc('reading_for_month');
    }
}
