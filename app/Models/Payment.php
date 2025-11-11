<?php

namespace App\Models;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_id',
        'meter_reading_id',
        'amount',
        'discount',
        'paid_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'discount' => 'decimal:2',
        'paid_at' => 'datetime',
        'reading_for_month' => 'date',
    ];

    protected $appends = ['total_value', 'remaining_after_payment'];

    // Relationships
    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function meterReading()
    {
        return $this->belongsTo(MeterReading::class);
    }

    // Accessor for total value (amount + discount)
    public function getTotalValueAttribute(): float
    {
        return (float)($this->amount ?? 0) + (float)($this->discount ?? 0);
    }

    // Apply payment to meter reading with validation
    public function applyToReading(): void
    {
        if (!$this->meterReading) {
            throw new \Exception('لا يمكن تطبيق الدفعة على قراءة غير موجودة');
        }

        // Check if reading is completed
        if (is_null($this->meterReading->reading_date)) {
            throw new \Exception('لا يمكن تطبيق الدفعة على قراءة غير مكتملة');
        }

        // Allow negative remaining_amount (overpayment/credit)
        $newRemainingAmount = $this->meterReading->remaining_amount - $this->total_value;
        
        $this->meterReading->update([
            'remaining_amount' => $newRemainingAmount 
        ]);
    }

    public function getRemainingAfterPaymentAttribute(): float
    {
        if (!$this->meterReading) {
            return 0;
        }

        $totalPaid = Payment::forReading($this->meter_reading_id)
            ->where('paid_at', '<=', $this->paid_at)
            ->sum(DB::raw('amount + discount'));

        $remaining = $this->meterReading->total_due - $totalPaid;
        
        return $remaining;
    }

    // Scopes
    public function scopeForClient($query, $clientId)
    {
        return $query->where('client_id', $clientId);
    }

    public function scopeForReading($query, $readingId)
    {
        return $query->where('meter_reading_id', $readingId);
    }

    public function scopeForCompletedReadings($query)
    {
        return $query->whereHas('meterReading', function ($q) {
            $q->whereNotNull('reading_date');
        });
    }

    public function scopeRecent($query)
    {
        return $query->orderByDesc('paid_at');
    }
}