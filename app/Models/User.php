<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Models\Client;
use App\Models\KilowattPrice;
use App\Models\UserPhoneNumber;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'password',
    ];


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

    public function clients()
    {
        return $this->hasMany(Client::class);
    }

    public function phoneNumbers()
    {
        return $this->hasMany(UserPhoneNumber::class, 'user_id', 'id');
    }

    public function kilowattPrice()
    {
        return $this->hasOne(KilowattPrice::class);
    }

    public function generators() 
    {
        return $this->hasMany(Generator::class);
    }

    public function meterCategories()
    {
        return $this->hasMany(MeterCategory::class);
    }

    public function exchangeRates()
    {
        return $this->hasOne(ExchangeRate::class);
    }

}
