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

    // ⚙️ Computed Attributes
    public function getConsumptionAttribute()
    {
        return $this->current_meter - $this->previous_meter;
    }

    public function getTotalDueAttribute()
    {
        return $this->amount + $this->maintenance_cost;
    }

    // Update reading status based on remaining amount
    public function updateStatus()
    {
        if ($this->remaining_amount <= 0) {
            $this->status = 'paid';
        } else {
            $this->status = 'unpaid';
        }
        $this->save();
    }

    // 🔍 Scopes
    public function scopeUnpaid($query)
    {
        return $query->where('status', 'unpaid');
    }

    public function scopeOldestFirst($query)
    {
        return $query->orderBy('reading_for_month', 'asc');
    }

    public function scopeForMonth($query, $month)
    {
        return $query->whereMonth('reading_for_month', $month->month)
                     ->whereYear('reading_for_month', $month->year);
    }
}