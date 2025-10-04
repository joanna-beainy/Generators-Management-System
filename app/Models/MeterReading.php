<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MeterReading extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'client_id',
        'previous_meter',
        'current_meter',
        'amount',
        'remaining_amount',
        'maintenance_cost',
        'reading_date',
        'reading_for_month',
        'status',
    ];

    protected $casts = [
        'reading_date' => 'datetime',
        'reading_for_month' => 'date',
    ];

    protected $appends = ['consumption', 'total_due'];

    // 🔗 Relationships
    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    // 📊 Computed: Consumption
    public function getConsumptionAttribute()
    {
        return $this->current_meter - $this->previous_meter;
    }

    // 🔍 Scope: Unpaid readings
    public function scopeUnpaid($query)
    {
        return $query->where('status', 'unpaid');
    }

    // 📅 Scope: Filter by month
    public function scopeForMonth($query, $month)
    {
        return $query->whereMonth('reading_for_month', $month->month)
                     ->whereYear('reading_for_month', $month->year);
    }
}
