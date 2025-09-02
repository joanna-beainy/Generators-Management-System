<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use App\Models\UserPhoneNumber;
use App\Models\KilowattPrice;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'password',
    ];

    protected static function booted()
    {
        // When a User is created, create a corresponding Kilowatt record
        static::created(function ($user) {
            if (!$user->is_admin){
                KilowattPrice::create([
                    'user_id' => $user->id,  // Associate kilowatt with the newly created user
                    'price' => 0,  // Default price for the kilowatt (can be customized)
                ]);
            }
        });

        static::deleting(function ($user) {
            if ($user->isForceDeleting()) {
                // Force delete related entities
                $user->phoneNumbers()->forceDelete();
                // $user->customers()->forceDelete();
                $user->kilowattPrice()->forceDelete();
                // $user->meterCategories()->forceDelete();
                // $user->generators()->forceDelete();
            } else {
                // Soft delete related entities
                $user->phoneNumbers()->delete();
                // $user->customers()->delete();
                $user->kilowattPrice()->delete();
                // $user->meterCategories()->delete();
                // $user->generators()->delete();
            }
        });

        static::restoring(function ($user) {
            // Restore related entities
            $user->phoneNumbers()->withTrashed()->restore();
            // $user->customers()->withTrashed()->restore();
            $user->kilowattPrice()->withTrashed()->restore();
            // $user->meterCategories()->withTrashed()->restore();
            // $user->generators()->withTrashed()->restore();
        });
    }


    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'password' => 'hashed',
        ];
    }

    public function phoneNumbers()
    {
        return $this->hasMany(UserPhoneNumber::class, 'user_id', 'id');
    }

    public function kilowattPrice()
    {
        return $this->hasOne(KilowattPrice::class);
    }

}
