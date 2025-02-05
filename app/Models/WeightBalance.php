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
        'weights',
    ];

    protected $casts = [
        'weights' => 'json',
    ];

    public function flight(): BelongsTo
    {
        return $this->belongsTo(Flight::class);
    }
}
