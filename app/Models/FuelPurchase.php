<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FuelPurchase extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'company',
        'liters_purchased',
        'remaining_liters',
        'total_price',
        'description',
    ];

    protected $casts = [
        'total_price' => 'decimal:2',
    ];

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function consumptions()
    {
        return $this->belongsToMany(FuelConsumption::class, 'fuel_consumption_purchase')
            ->withPivot('liters_deducted')
            ->withTimestamps();
    }

    // Scopes
    public function scopeAvailable($query)
    {
        return $query->where('remaining_liters', '>', 0)->orderBy('created_at');
    }

    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }


    /**
     * Check if any liters have been consumed from this purchase
     */
    public function getHasConsumptionAttribute()
    {
        return $this->remaining_liters != $this->liters_purchased;
    }
}