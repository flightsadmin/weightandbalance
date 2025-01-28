<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AircraftType extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'manufacturer',
        'max_passengers',
        'cargo_capacity',
        'max_fuel_capacity',
        'empty_weight',
        'max_zero_fuel_weight',
        'max_takeoff_weight',
        'max_landing_weight',
        'max_range',
        'category',
        'max_deck_crew',
        'max_cabin_crew',
    ];

    /**
     * Get the aircraft of this type
     */
    public function aircraft(): HasMany
    {
        return $this->hasMany(Aircraft::class);
    }

    /**
     * Get the holds for this aircraft type
     */
    public function holds(): HasMany
    {
        return $this->hasMany(Hold::class)->orderBy('position');
    }

    /**
     * Get all available positions in all holds
     */
    public function getAllPositions()
    {
        return $this->holds
            ->where('is_active', true)
            ->flatMap(fn($hold) => $hold->getPositions())
            ->values();
    }
}