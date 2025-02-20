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
        $pantries = $this->getSetting('pantries', []);
        $pantryCode = strtoupper($pantryCode);

        return $pantries[$pantryCode] ?? [
            'name' => '-',
            'code' => $pantryCode,
            'weight' => 0,
            'index' => 0
        ];
    }

    public function getAllPantries()
    {
        return collect($this->getSetting('pantries', []));
    }

    public function seats(): HasMany
    {
        return $this->hasMany(Seat::class);
    }

    public function getFuelIndex($weight)
    {
        $envelope = $this->envelopes()->where('name', 'FUEL')->first();

        if (!$envelope || !$envelope->points) {
            return 0;
        }

        $points = collect($envelope->points);

        $exactMatch = $points->firstWhere('weight', $weight);
        if ($exactMatch) {
            return $exactMatch['index'];
        }

        return $points->sortBy(function ($point) use ($weight) {
            return abs($point['weight'] - $weight);
        })->first()['index'];
    }

    public function getFuelIndexes($takeoffFuel, $landingFuel)
    {
        return [
            'takeoff' => $this->getFuelIndex($takeoffFuel),
            'landing' => $this->getFuelIndex($landingFuel),
        ];
    }

    public function calculateMac($weight, $index)
    {
        $macSettings = $this->getMacSettings();

        // Formula: %MAC = ((C * (I-K))/W + Ref.Sta - LEMAC) / (MAC/100)
        return
            (($macSettings['c_constant'] * ($index - $macSettings['k_constant'])) / $weight
                + $macSettings['ref_sta_at']
                - $macSettings['lemac_at'])
            / ($macSettings['length_of_mac'] / 100);
    }

    public function getMacSettings()
    {
        $macSettings = $this->getSetting('mac_settings', [
            'k_constant' => 50,
            'c_constant' => 1000,
            'length_of_mac' => 4.194,
            'lemac_at' => 17.8015,
            'ref_sta_at' => 18.850
        ]);

        return $macSettings;
    }

    public function getZfwMac($weight, $index)
    {
        return $this->calculateMac($weight, $index);
    }

    public function getTowMac($weight, $index)
    {
        return $this->calculateMac($weight, $index);
    }

    public function getLdwMac($weight, $index)
    {
        return $this->calculateMac($weight, $index);
    }

    public function getCrewIndex($deckCrewCount, $cabinCrewCount, $deckCrewWeight, $cabinCrewWeight)
    {
        $deckCrewSeating = $this->crewSeating()->where('position', 'deck_crew')->first();

        $deckCrewIndex = $deckCrewCount * $deckCrewWeight * $deckCrewSeating->index_per_kg;

        $distribution = $this->crewDistributions()->where('crew_count', $cabinCrewCount)->first();

        if (!$distribution) {
            return $deckCrewIndex;
        }

        $cabinCrewIndex = 0;
        $cabinSeating = $this->crewSeating()->where('position', 'cabin_crew')->get();
        foreach ($distribution->distribution as $index => $crewCount) {
            if ($crewCount > 0 && isset($cabinSeating[$index])) {
                $seating = $cabinSeating[$index];
                $locationIndex = $crewCount * $cabinCrewWeight * $seating->index_per_kg;
                $cabinCrewIndex += $locationIndex;
            }
        }

        return $deckCrewIndex + $cabinCrewIndex;
    }

    public function getCrewIndexes($crewMembers)
    {
        $crewMembers = explode('/', $crewMembers);
        $deckCrewCount = (int) $crewMembers[0];
        $cabinCrewCount = (int) $crewMembers[1];

        $deckCrewWeight = $this->getSetting('standard_cockpit_crew_weight', 85);
        $cabinCrewWeight = $this->getSetting('standard_cabin_crew_weight', 75);

        return [
            'index' => $this->getCrewIndex($deckCrewCount, $cabinCrewCount, $deckCrewWeight, $cabinCrewWeight),
            'weight' => ($deckCrewCount * $deckCrewWeight) + ($cabinCrewCount * $cabinCrewWeight),
        ];
    }
}
