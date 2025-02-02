<?php

namespace App\Enums;

enum Settings
{
    case STANDARD_CREW_WEIGHT = 'standard_crew_weight';
    case STANDARD_BAGGAGE_WEIGHT = 'standard_baggage_weight';
    case STANDARD_PASSENGER_WEIGHT = 'standard_passenger_weight';
    case STANDARD_PANTRY_WEIGHT = 'standard_pantry_weight';

    public function label(): string
    {
        return match ($this) {
            self::STANDARD_CREW_WEIGHT => 'Standard Crew Weight',
            self::STANDARD_BAGGAGE_WEIGHT => 'Standard Baggage Weight',
            self::STANDARD_PASSENGER_WEIGHT => 'Standard Passenger Weight',
            self::STANDARD_PANTRY_WEIGHT => 'Standard Pantry Weight',
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::STANDARD_CREW_WEIGHT => 'The standard weight for crew members.',
            self::STANDARD_BAGGAGE_WEIGHT => 'The standard weight for baggage.',
            self::STANDARD_PASSENGER_WEIGHT => 'The standard weight for passengers.',
            self::STANDARD_PANTRY_WEIGHT => 'The standard weight for pantry items.',
        };
    }

    public function type(): string
    {
        return match ($this) {
            self::STANDARD_CREW_WEIGHT => 'number',
            self::STANDARD_BAGGAGE_WEIGHT => 'number',
            self::STANDARD_PASSENGER_WEIGHT => 'number',
            self::STANDARD_PANTRY_WEIGHT => 'number',
        };
    }

    public function default(): int
    {
        return match ($this) {
            self::STANDARD_CREW_WEIGHT => 85,
            self::STANDARD_BAGGAGE_WEIGHT => 20,
            self::STANDARD_PASSENGER_WEIGHT => 84,
            self::STANDARD_PANTRY_WEIGHT => 250,
        };
    }
}
