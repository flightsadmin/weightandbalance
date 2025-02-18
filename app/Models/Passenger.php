<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Passenger extends Model
{
    use HasFactory;

    protected $table = 'passengers';

    protected $fillable = [
        'flight_id',
        'seat_id',
        'name',
        'type',
        'ticket_number',
        'reservation_number',
        'acceptance_status',
        'boarding_status',
        'attributes',
    ];

    protected $casts = [
        'attributes' => 'array',
    ];

    protected $attributes = [
        'attributes' => '{"wchr": false, "wchs": false, "wchc": false, "exst": false, "stcr": false, "deaf": false, "blind": false, "dpna": false, "meda": false, "infant": false
        }'
    ];

    public function flight(): BelongsTo
    {
        return $this->belongsTo(Flight::class);
    }

    public function baggage(): HasMany
    {
        return $this->hasMany(Baggage::class);
    }

    public function seat(): BelongsTo
    {
        return $this->belongsTo(Seat::class);
    }

    public function getFlightSeatAttribute()
    {
        return $this->seat?->designation ?? 'No Seat';
    }
}
