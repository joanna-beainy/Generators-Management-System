<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Client extends Model
{
    use HasFactory;

    protected $fillable = [
        'first_name', 'father_name', 'last_name',
        'phone_number', 'address',
        'generator_id', 'meter_category_id', 'user_id',
        'initial_meter', 'is_active', 'is_offered',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_offered' => 'boolean',
        'initial_meter' => 'integer',
    ];

    protected $appends = ['full_name'];

    // Safely generate full name even if some fields are null 
    public function getFullNameAttribute(): string
    {
        return trim(collect([$this->first_name, $this->father_name, $this->last_name])
            ->filter()
            ->implode(' '));
    }

    // Relationships
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

    public function meterReadings()
    {
        return $this->hasMany(MeterReading::class);
    }

    public function latestReading()
    {
        return $this->hasOne(MeterReading::class)->latestOfMany();
    }


    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function maintenances()
    {
        return $this->hasMany(Maintenance::class);
    }

    // Latest remaining amount from last reading where reading_date not null
    public function getTotalRemainingAmountAttribute(): float
    {
        $latestReading = $this->meterReadings()
            ->whereNotNull('reading_date')
            ->orderByDesc('reading_for_month')
            ->first();

        return $latestReading ? (float)$latestReading->remaining_amount : 0.0;
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    // not offered clients
    public function scopeNotOffered($query)
    {
        return $query->where('is_offered', false);
    }

    // Flexible search that tolerates missing name parts 
    public function scopeSearch($query, string $term)
    {
        $term = trim($term);

        if ($term === '') {
            return $query;
        }

        $words = preg_split('/\s+/', $term);
        $count = count($words);

        return $query->where(function ($q) use ($words, $count, $term) {
            // Always allow ID search
            $q->where('id', $term);

            // Single word: first name partial
            if ($count === 1) {
                $q->orWhere('first_name', 'like', "%{$words[0]}%");
            }

            // Two words: could be
            // (a) multi-word first name
            // (b) first + father/last
            elseif ($count === 2) {
                $q->orWhere('first_name', 'like', "%{$words[0]} {$words[1]}%")
                ->orWhere(function ($sub) use ($words) {
                    $sub->where('first_name', 'like', "%{$words[0]}%")
                        ->where(function ($s) use ($words) {
                            $s->where('father_name', 'like', "%{$words[1]}%")
                                ->orWhere('last_name', 'like', "%{$words[1]}%");
                        });
                });
            }

            // Three or more words
            else {
                $joined = implode(' ', $words);

                $q->orWhere('first_name', 'like', "%{$joined}%") // matches long first names
                ->orWhere(function ($sub) use ($words, $count) {
                    // if user wrote first + father + last
                    if ($count >= 3) {
                        $sub->where('first_name', 'like', "%{$words[0]}%")
                            ->where('father_name', 'like', "%{$words[1]}%")
                            ->where('last_name', 'like', "%{$words[2]}%");
                    }
                });
            }
        });
    }


}
