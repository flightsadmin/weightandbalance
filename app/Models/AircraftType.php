<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AircraftType extends Model
{
    use HasFactory;

    protected $fillable = [
        'airline_id',
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
        'category',
        'max_deck_crew',
        'max_cabin_crew',
    ];

    /**
     * Get the settings for this aircraft type
     */
    public function settings()
    {
        return $this->morphMany(Setting::class, 'settingable');
    }

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

    public function getSetting($key, $default = null)
    {
        return $this->settings()->where('key', $key)->first()?->typed_value ?? $default;
    }

    public function airline(): BelongsTo
    {
        return $this->belongsTo(Airline::class);
    }

    public function airlines(): BelongsToMany
    {
        return $this->belongsToMany(Airline::class)->withTimestamps();
    }

    /**
     * Get all active airlines using this aircraft type
     */
    public function activeAirlines(): BelongsToMany
    {
        return $this->airlines()->wherePivot('is_active', true);
    }

    public function cabinZones(): HasMany
    {
        return $this->hasMany(CabinZone::class)->orderBy('index');
    }

    public function crewSeating(): HasMany
    {
        return $this->hasMany(CrewSeating::class);
    }

    public function crewDistributions(): HasMany
    {
        return $this->hasMany(CrewDistribution::class);
    }

    public function envelopes(): HasMany
    {
        return $this->hasMany(Envelope::class);
    }

    public function getPantryDetails($pantryCode)
    {
        $pantryCode = strtolower($pantryCode);

        return [
            'name' => $this->getSetting("pantry_{$pantryCode}_name", ''),
            'weight' => $this->getSetting("pantry_{$pantryCode}_weight", 0),
            'index' => $this->getSetting("pantry_{$pantryCode}_index", 0),
        ];
    }

    public function getAllPantries()
    {
        return collect($this->settings()
            ->where('key', 'like', 'pantry_%_name')
            ->get())
            ->map(function ($setting) {
                $code = str_replace(['pantry_', '_name'], '', $setting->key);

                return array_merge(
                    ['code' => $code],
                    $this->getPantryDetails($code)
                );
            });
    }

    public function seats(): HasMany
    {
        return $this->hasMany(Seat::class);
    }
}
