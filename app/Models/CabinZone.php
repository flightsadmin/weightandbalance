<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CabinZone extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'max_capacity', 'index', 'arm'];

    public function aircraftType()
    {
        return $this->belongsTo(AircraftType::class);
    }
}
