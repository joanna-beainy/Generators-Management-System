<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Client extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'first_name', 'father_name', 'last_name',
        'phone_number', 'address',
        'generator_id', 'meter_category_id', 'user_id'
    ];

    public function fullName()
    {
        return "{$this->first_name} {$this->father_name} {$this->last_name}";
    }
    
    public function generator()
    {
        return $this->belongsTo(Generator::class);
    }

    public function meterCategory()
    {
        return $this->belongsTo(MeterCategory::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

