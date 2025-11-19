<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FuelConsumptionPurchase extends Model
{
    protected $table = 'fuel_consumption_purchase';

    protected $fillable = [
        'fuel_consumption_id',
        'fuel_purchase_id',
        'liters_deducted',
    ];

    public function consumption()
    {
        return $this->belongsTo(FuelConsumption::class, 'fuel_consumption_id');
    }

    public function purchase()
    {
        return $this->belongsTo(FuelPurchase::class, 'fuel_purchase_id');
    }
}