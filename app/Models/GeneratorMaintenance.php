<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GeneratorMaintenance extends Model
{
    use HasFactory;

    protected $table = 'generators_maintenances';

    protected $fillable = [
        'user_id',
        'generator_id',
        'amount',
        'description',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
    ];

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function generator()
    {
        return $this->belongsTo(Generator::class);
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
}