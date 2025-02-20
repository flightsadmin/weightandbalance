<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Airline extends Model
{
    use HasFactory;

    protected $table = 'airlines';

    protected $fillable = [
        'name',
        'iata_code',
        'icao_code',
        'country',
        'address',
        'phone',
        'email',
        'description',
        'active',
    ];

    protected $casts = [
        'active' => 'boolean',
    ];

    public const STANDARD_PASSENGER_WEIGHT = 'standard_passenger_weight';

    public const STANDARD_MALE_PASSENGER_WEIGHT = 'standard_male_passenger_weight';

    public const STANDARD_FEMALE_PASSENGER_WEIGHT = 'standard_female_passenger_weight';

    public const STANDARD_CHILD_PASSENGER_WEIGHT = 'standard_child_passenger_weight';

    public const STANDARD_INFANT_PASSENGER_WEIGHT = 'standard_infant_passenger_weight';

    public const STANDARD_COCKPIT_CREW_WEIGHT = 'standard_cockpit_crew_weight';

    public const STANDARD_CABIN_CREW_WEIGHT = 'standard_cabin_crew_weight';

    public const STANDARD_PANTRY_WEIGHT = 'standard_pantry_weight';

    public function aircraft(): HasMany
    {
        return $this->hasMany(Aircraft::class);
    }

    public function flights(): HasMany
    {
        return $this->hasMany(Flight::class);
    }

    public function settings(): MorphMany
    {
        return $this->morphMany(Setting::class, 'settingable');
    }

    public function aircraftTypes(): HasMany
    {
        return $this->hasMany(AircraftType::class);
    }

    public function scopeActive($query)
    {
        return $query->where('active', true);
    }

    public function getStandardPassengerWeight($type)
    {
        $settings = $this->getSettings('general');
        
        return match ($type) {
            'male' => (int) ($settings['standard_male_passenger_weight'] ?? 88),
            'female' => (int) ($settings['standard_female_passenger_weight'] ?? 70),
            'child' => (int) ($settings['standard_child_passenger_weight'] ?? 35),
            'infant' => (int) ($settings['standard_infant_passenger_weight'] ?? 10),
            default => (int) ($settings['standard_passenger_weight'] ?? 84),
        };
    }

    public function getStandardCockpitCrewWeight(): int
    {
        $settings = $this->getSettings('general');
        return (int) ($settings['standard_cockpit_crew_weight'] ?? 85);
    }

    public function getStandardCabinCrewWeight(): int
    {
        $settings = $this->getSettings('general');
        return (int) ($settings['standard_cabin_crew_weight'] ?? 75);
    }

    public function getStandardPantryWeight(): int
    {
        $settings = $this->getSettings('general');
        return (int) ($settings['standard_pantry_weight'] ?? 250);
    }

    public function getSettings($category = null)
    {
        $settings = $this->getSetting('airline_settings', [
            'general' => [
                'standard_passenger_weight' => 84,
                'standard_crew_weight' => 85,
                'standard_baggage_weight' => 13,
                'standard_fuel_density' => 0.89,
            ],
            'operations' => [
                'checkin_open_time' => 180,
                'checkin_close_time' => 45,
                'boarding_open_time' => 60,
                'boarding_close_time' => 15,
            ],
            'cargo' => [
                'dangerous_goods_allowed' => false,
                'live_animals_allowed' => false,
                'max_cargo_piece_weight' => 150,
                'max_baggage_piece_weight' => 32,
            ],
            'notifications' => [
                'enable_email_notifications' => true,
                'enable_sms_notifications' => false,
                'notification_email' => '',
                'notification_phone' => '',
            ]
        ]);

        return $category ? ($settings[$category] ?? []) : $settings;
    }

    public function updateSettings($category, $key, $value)
    {
        $settings = $this->getSettings();
        $settings[$category][$key] = $value;

        return $this->settings()->updateOrCreate(
            [
                'key' => 'airline_settings',
                'airline_id' => $this->id
            ],
            [
                'value' => json_encode($settings),
                'type' => 'json',
                'description' => 'Airline Configuration Settings'
            ]
        );
    }

    public function getSetting($key, $default = null)
    {
        $setting = $this->settings()->where('key', $key)->first();
        return $setting ? $setting->typed_value : $default;
    }
}
