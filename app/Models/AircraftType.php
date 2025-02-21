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

    public function getCrewSettings()
    {
        return $this->getSetting('crew_settings', [
            'seating' => [
                'deck_crew' => [
                    'name' => 'Flight Deck',
                    'index_per_kg' => 0.00324,
                    'max_crew' => 2,
                    'is_deck_crew' => true
                ],
                'cabin_crew_1' => [
                    'name' => 'Forward Cabin',
                    'index_per_kg' => 0.00324,
                    'max_crew' => 2,
                    'is_deck_crew' => false
                ],
                'cabin_crew_2' => [
                    'name' => 'Mid Cabin',
                    'index_per_kg' => 0.00401,
                    'max_crew' => 2,
                    'is_deck_crew' => false
                ],
                'cabin_crew_3' => [
                    'name' => 'Aft Cabin',
                    'index_per_kg' => 0.01048,
                    'max_crew' => 2,
                    'is_deck_crew' => false
                ]
            ],
            'distributions' => [
                2 => [1, 1, 0],  // 2 cabin crew: 1 forward, 1 mid
                3 => [1, 1, 1],  // 3 cabin crew: distributed evenly
                4 => [2, 1, 1],  // 4 cabin crew: 2 forward, 1 mid, 1 aft
                5 => [2, 2, 1],  // 5 cabin crew: 2 forward, 2 mid, 1 aft
                6 => [3, 2, 1]   // 6 cabin crew: 3 forward, 2 mid, 1 aft
            ]
        ]);
    }

    public function getCrewIndexes($crewConfig)
    {
        if (!$crewConfig) {
            return ['index' => 0, 'weight' => 0];
        }

        [$deckCrew, $cabinCrew] = explode('/', $crewConfig);
        $settings = $this->getCrewSettings();
        $airline = $this->airline;

        // Calculate deck crew index and weight
        $deckCrewWeight = $deckCrew * $airline->getStandardCockpitCrewWeight();
        $deckCrewIndex = $deckCrewWeight * $settings['seating']['deck_crew']['index_per_kg'];

        // Get distribution for cabin crew count
        $distribution = $settings['distributions'][$cabinCrew] ?? [];

        // Calculate cabin crew index and weight
        $cabinCrewWeight = 0;
        $cabinCrewIndex = 0;

        foreach ($distribution as $position => $count) {
            if ($count > 0) {
                $seating = array_values($settings['seating'])[min($position + 1, count($settings['seating']) - 1)];
                $weight = $count * $airline->getStandardCabinCrewWeight();
                $cabinCrewWeight += $weight;
                $cabinCrewIndex += $weight * $seating['index_per_kg'];
            }
        }

        return [
            'index' => $deckCrewIndex + $cabinCrewIndex,
            'weight' => $deckCrewWeight + $cabinCrewWeight
        ];
    }
}
