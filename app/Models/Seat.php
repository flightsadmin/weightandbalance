<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Seat extends Model
{
    protected $fillable = [
        'aircraft_type_id',
        'cabin_zone_id',
        'row',
        'column',
        'designation', // A, B, C, etc.
        'type', // economy, business, first
        'is_exit',
        'is_blocked',
        'notes',
    ];

    protected $casts = [
        'is_exit' => 'boolean',
        'is_blocked' => 'boolean',
    ];

    public function aircraftType(): BelongsTo
    {
        return $this->belongsTo(AircraftType::class);
    }

    public function cabinZone(): BelongsTo
    {
        return $this->belongsTo(CabinZone::class);
    }
}