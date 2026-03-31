<?php

namespace App\Models;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Payment extends Model
{
    use HasFactory;
    use SoftDeletes;

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
        'deleted_at' => 'datetime',
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

        $this->meterReading->decrement('remaining_amount', $this->total_value);
    }

    public function deleteWithAutoHandling(): ?bool
    {
        if (!$this->belongsToLatestCompletedReading()) {
            throw new \Exception('يمكن حذف دفعات آخر قراءة مكتملة فقط.');
        }

        return DB::transaction(function () {
            $this->meterReading?->increment('remaining_amount', $this->total_value);
            $deleted = $this->delete();

            return $deleted;
        });
    }

    public static function recordForLatestCompletedReading(int $clientId, float $amount, float $discount = 0, $paidAt = null): ?self
    {
        $latestCompletedReading = MeterReading::latestCompletedForClient($clientId);

        if (!$latestCompletedReading) {
            return null;
        }

        return DB::transaction(function () use ($clientId, $latestCompletedReading, $amount, $discount, $paidAt) {
            $payment = self::create([
                'client_id' => $clientId,
                'meter_reading_id' => $latestCompletedReading->id,
                'amount' => $amount,
                'discount' => $discount,
                'paid_at' => $paidAt ?? now(),
            ]);

            $payment->applyToReading();

            return $payment;
        });
    }

    public function belongsToLatestCompletedReading(): bool
    {
        if (!$this->meterReading || is_null($this->meterReading->reading_date)) {
            return false;
        }

        $latestCompletedReading = MeterReading::latestCompletedForClient($this->client_id);

        return (bool) $latestCompletedReading
            && (int) $latestCompletedReading->id === (int) $this->meter_reading_id;
    }

    public function getRemainingAfterPaymentAttribute(): float
    {
        if (!$this->meterReading) {
            return 0;
        }

        $totalPaid = Payment::withTrashed()
            ->forReading($this->meter_reading_id)
            ->where('paid_at', '<=', $this->paid_at)
            ->where(function ($query) {
                $query->whereNull('deleted_at')
                    ->orWhere('deleted_at', '>', $this->paid_at);
            })
            ->sum(DB::raw('amount + discount'));

        $remaining = $this->historicalTotalDue() - $totalPaid;
        
        return $remaining;
    }

    private function historicalTotalDue(): float
    {
        $reading = $this->meterReading;

        $maintenanceAddedAfterPayment = Maintenance::query()
            ->where('applied_meter_reading_id', $this->meter_reading_id)
            ->where('created_at', '>', $this->paid_at)
            ->sum('amount');

        $historicalMaintenanceCost = (float) $reading->maintenance_cost - (float) $maintenanceAddedAfterPayment;

        return (float) $reading->amount
            + max(0, $historicalMaintenanceCost)
            + (float) $reading->previous_balance;
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

    public function scopeRecent($query)
    {
        return $query->orderByDesc('paid_at');
    }
}
