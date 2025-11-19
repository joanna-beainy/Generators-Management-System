<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class FuelConsumption extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'generator_id',
        'liters_consumed',
        'notes',
    ];

    // Relationships
    public function generator()
    {
        return $this->belongsTo(Generator::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function purchases()
    {
        return $this->belongsToMany(FuelPurchase::class, 'fuel_consumption_purchase')
            ->withPivot('liters_deducted')
            ->withTimestamps();
    }

    // Scopes
    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeForGenerator($query, $generatorId)
    {
        return $query->where('generator_id', $generatorId);
    }

    /**
     * Handle deduction logic automatically when creating a new consumption.
     * It deducts liters from oldest available purchases until satisfied.
     */
    public static function recordConsumption($userId, $generatorId, $liters, $notes = null)
    {
        return DB::transaction(function () use ($userId, $generatorId, $liters, $notes) {
            $remainingToDeduct = $liters;

            $availablePurchases = FuelPurchase::where('user_id', $userId)
                ->where('remaining_liters', '>', 0)
                ->orderBy('created_at')
                ->lockForUpdate()
                ->get();

            if ($availablePurchases->sum('remaining_liters') < $liters) {
                throw new \Exception('الوقود المتاح غير كافٍ لهذه العملية.');
            }

            $consumption = self::create([
                'user_id' => $userId,
                'generator_id' => $generatorId,
                'liters_consumed' => $liters,
                'notes' => $notes,
            ]);

            foreach ($availablePurchases as $purchase) {
                if ($remainingToDeduct <= 0) break;

                $deduct = min($remainingToDeduct, $purchase->remaining_liters);

                $purchase->decrement('remaining_liters', $deduct);

                $consumption->purchases()->attach($purchase->id, [
                    'liters_deducted' => $deduct,
                ]);

                $remainingToDeduct -= $deduct;
            }

            return $consumption;
        });
    }

    /**
     * When deleting a consumption, restore deducted liters back to the linked purchases.
     */
    protected static function booted()
    {
        static::deleting(function ($consumption) {
            DB::transaction(function () use ($consumption) {
                foreach ($consumption->purchases as $purchase) {
                    $purchase->increment('remaining_liters', $purchase->pivot->liters_deducted);
                }

                $consumption->purchases()->detach();
            });
        });
    }
}