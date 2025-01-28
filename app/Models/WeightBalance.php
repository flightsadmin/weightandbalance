<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WeightBalance extends Model
{
    protected $table = 'weight_balances';
    use HasFactory;

    protected $fillable = [
        'flight_id',
        'zero_fuel_weight',
        'takeoff_fuel_weight',
        'takeoff_weight',
        'landing_fuel_weight',
        'landing_weight',
        'passenger_weight_total',
        'baggage_weight_total',
        'cargo_weight_total',
        'crew_weight_total',
        'center_of_gravity',
        'within_limits',
        'notes'
    ];

    protected $casts = [
        'zero_fuel_weight' => 'integer',
        'takeoff_fuel_weight' => 'integer',
        'takeoff_weight' => 'integer',
        'landing_fuel_weight' => 'integer',
        'landing_weight' => 'integer',
        'passenger_weight_total' => 'integer',
        'baggage_weight_total' => 'integer',
        'cargo_weight_total' => 'integer',
        'crew_weight_total' => 'integer',
        'center_of_gravity' => 'decimal:2',
        'within_limits' => 'boolean'
    ];

    public function flight(): BelongsTo
    {
        return $this->belongsTo(Flight::class);
    }
}
