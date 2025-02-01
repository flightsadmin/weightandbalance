<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class HoldPosition extends Model
{
    use HasFactory;

    protected $fillable = [
        'hold_id',
        'code',      // e.g., '31L', '32R'
        'row',       // e.g., 3
        'side',      // 'L' or 'R' or null for center
        'max_weight',
        'index',
        'is_active'
    ];

    protected $casts = [
        'max_weight' => 'integer',
        'index' => 'float',
        'is_active' => 'boolean'
    ];

    public function hold()
    {
        return $this->belongsTo(Hold::class);
    }

    public function getFullCode()
    {
        return $this->hold->code . $this->code;
    }
}