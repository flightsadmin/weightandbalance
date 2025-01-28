<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Fuel extends Model
{
    use HasFactory;

    protected $table = 'fuel';

    protected $fillable = [
        'flight_id',
        'taxi_fuel',
        'trip_fuel',
        'contingency_fuel',
        'alternate_fuel',
        'final_reserve_fuel',
        'additional_fuel',
        'total_fuel',
        'notes'
    ];

    protected $casts = [
        'taxi_fuel' => 'float',
        'trip_fuel' => 'float',
        'contingency_fuel' => 'float',
        'alternate_fuel' => 'float',
        'final_reserve_fuel' => 'float',
        'additional_fuel' => 'float',
        'total_fuel' => 'float',
    ];

    public function flight(): BelongsTo
    {
        return $this->belongsTo(Flight::class);
    }
}
