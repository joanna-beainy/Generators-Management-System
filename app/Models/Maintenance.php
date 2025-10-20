<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Carbon\Carbon;

class Maintenance extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_id',
        'amount',
        'description',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /* -------------------------------- Relationships ------------------------------- */

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    /* ---------------------------------- Accessors --------------------------------- */

    public function getTargetMonthAttribute()
    {
        // All maintenance added between the 1st and last day of a month belong to that month
        return $this->created_at->copy()->startOfMonth();
    }

    /* ----------------------------------- Scopes ----------------------------------- */

    public function scopeForMonth($query, Carbon $month)
    {
        return $query->whereYear('created_at', $month->year)
                     ->whereMonth('created_at', $month->month);
    }

    public function scopeForClient($query, $clientId)
    {
        return $query->where('client_id', $clientId);
    }

    /* ----------------------------------- Core Logic -------------------------------- */

    /**
     * Add maintenance and automatically update the matching meter reading.
     */
    public static function addWithAutoHandling($clientId, $amount, $description = null)
    {
        $now = Carbon::now();
        $targetMonth = $now->copy()->startOfMonth();

        // 1️⃣ Create maintenance record
        $maintenance = self::create([
            'client_id' => $clientId,
            'amount' => $amount,
            'description' => $description,
        ]);

        // 2️⃣ Find the corresponding meter reading for this month
        $reading = MeterReading::where('client_id', $clientId)
            ->whereDate('reading_for_month', $targetMonth)
            ->first();

        // 3️⃣ Update maintenance_cost and remaining_amount if reading exists
        if ($reading) {
            $reading->increment('maintenance_cost', $amount);
            $reading->increment('remaining_amount', $amount);
        }

        return $maintenance;
    }

    /**
     * Delete maintenance and reverse its effect on the meter reading.
     */
    public function deleteWithAutoHandling()
    {
        $targetMonth = $this->created_at->copy()->startOfMonth();

        $reading = MeterReading::where('client_id', $this->client_id)
            ->whereDate('reading_for_month', $targetMonth)
            ->first();

        if ($reading) {
            $reading->decrement('maintenance_cost', $this->amount);
            $reading->decrement('remaining_amount', $this->amount);
        }

        return $this->delete();
    }

    /* -------------------------- Helper / Display Functions ------------------------ */

    public function getArabicMonthName()
    {
        $months = [
            1 => 'كانون الثاني', 2 => 'شباط', 3 => 'آذار', 4 => 'نيسان',
            5 => 'أيار', 6 => 'حزيران', 7 => 'تموز', 8 => 'آب',
            9 => 'أيلول', 10 => 'تشرين الأول', 11 => 'تشرين الثاني', 12 => 'كانون الأول',
        ];

        return $months[$this->created_at->month] . ' ' . $this->created_at->year;
    }

    public function getFormattedAmountAttribute()
    {
        return number_format($this->amount, 0);
    }

    /**
     * Ensure this maintenance belongs to a specific user's client.
     */
    public function belongsToUser($userId)
    {
        return $this->client && $this->client->user_id === $userId;
    }
}
