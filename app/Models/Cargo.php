<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Cargo extends Model
{
    use HasFactory;

    protected $table = 'cargo';

    protected $fillable = [
        'flight_id',
        'awb_number',
        'type',
        'description',
        'weight',
        'container_id',
        'status',
        'special_instructions'
    ];

    protected $casts = [
        'weight' => 'integer',
        'volume' => 'integer'
    ];

    public function flight(): BelongsTo
    {
        return $this->belongsTo(Flight::class);
    }

    public function container(): BelongsTo
    {
        return $this->belongsTo(Container::class);
    }

    protected static function booted()
    {
        static::created(function ($cargo) {
            $cargo->container?->updateWeight();
        });

        static::updated(function ($cargo) {
            $cargo->container?->updateWeight();
        });

        static::deleted(function ($cargo) {
            $cargo->container?->updateWeight();
        });
    }
}
