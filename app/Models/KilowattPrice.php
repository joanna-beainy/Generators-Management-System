<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class KilowattPrice extends Model
{
    use HasFactory;
    use SoftDeletes;
    
    protected $fillable = ['user_id', 'price'];

    public $timestamps = true;

    // Relationship: each price belongs to one user
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
