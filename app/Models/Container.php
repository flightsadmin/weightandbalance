<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Container extends Model
{
    use HasFactory;

    protected $fillable = [
        'container_number',
        'flight_id',
        'type',
        'compartment',
        'status',
        'tare_weight',
        'weight',
        'max_weight',
        'notes',
    ];

    public function flight(): BelongsTo
    {
        return $this->belongsTo(Flight::class);
    }

    public function baggage(): HasMany
    {
        return $this->hasMany(Baggage::class);
    }

    public function cargo(): HasMany
    {
        return $this->hasMany(Cargo::class);
    }

    public function getTotalWeightAttribute()
    {
        return $this->baggage->sum('weight') + $this->cargo->sum('weight');
    }

    public function updateWeight()
    {
        $this->weight = $this->total_weight;
        $this->save();
    }
}
