<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class KilowattPrice extends Model
{
    use HasFactory;
    
    protected $fillable = ['user_id', 'price'];

    public $timestamps = true;

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
