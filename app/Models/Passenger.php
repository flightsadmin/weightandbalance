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
        'name',
        'type',
        'seat_number',
        'ticket_number',
        'acceptance_status',
        'boarding_status',
        'notes'
    ];

    public function flight(): BelongsTo
    {
        return $this->belongsTo(Flight::class);
    }

    public function baggage(): HasMany
    {
        return $this->hasMany(Baggage::class);
    }
}
