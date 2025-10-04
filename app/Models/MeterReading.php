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
        'maintenance_cost',
        'reading_date',
        'reading_for_month',
        'status',
    ];

    protected $casts = [
        'reading_date' => 'datetime',
        'reading_for_month' => 'date',
    ];

    protected $appends = ['consumption', 'total_due', 'remaining_amount'];

    // 🔗 Relationships
    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    // 🔗 Payments Relationship (via pivot)
    public function payments()
    {
        return $this->belongsToMany(Payment::class, 'payment_reading')
            ->withPivot('applied_amount')
            ->withTimestamps();
    }

    // ⚙️ Computed Attributes
    public function getConsumptionAttribute()
    {
        return $this->current_meter - $this->previous_meter;
    }

    public function getTotalDueAttribute()
    {
        return $this->amount + $this->maintenance_cost;
    }

    public function getRemainingAmountAttribute()
    {
        return max(0, $this->total_due - $this->payments()->sum('payment_reading.applied_amount'));
    }

    // 🔍 Scopes
    public function scopeUnpaid($query)
    {
        return $query->where('status', 'unpaid');
    }

    public function scopeForMonth($query, $month)
    {
        return $query->whereMonth('reading_for_month', $month->month)
                     ->whereYear('reading_for_month', $month->year);
    }
}
