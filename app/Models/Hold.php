<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Hold extends Model
{
    use HasFactory;

    protected $fillable = [
        'aircraft_type_id',
        'name',
        'code',
        'position',
        'max_weight',
        'index',
        'is_active'
    ];

    protected $casts = [
        'max_weight' => 'integer',
        'index' => 'float',
        'is_active' => 'boolean'
    ];

    public function aircraftType(): BelongsTo
    {
        return $this->belongsTo(AircraftType::class);
    }

    public function positions(): HasMany
    {
        return $this->hasMany(HoldPosition::class)->orderBy('row')->orderBy('side');
    }

    public function getCurrentWeight($containerPositions, $containers)
    {
        return $containers->whereIn('id', array_keys($containerPositions))
            ->filter(fn($container) => str_starts_with($containerPositions[$container->id], $this->code))
            ->sum('weight');
    }

    public function isOverweight($currentWeight)
    {
        return $currentWeight > $this->max_weight;
    }
}