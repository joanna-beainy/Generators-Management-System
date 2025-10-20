<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExchangeRate extends Model
{
    protected $fillable = ['user_id', 'exchange_rate'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public static function getCurrentRate($userId)
    {
        return self::where('user_id', $userId)->value('exchange_rate') ?? 89500;
    }
}