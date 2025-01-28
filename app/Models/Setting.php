<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Setting extends Model
{
    protected $table = 'settings';
    protected $fillable = [
        'airline_id',
        'key',
        'value',
        'description',
        'type'
    ];

    public function airline(): BelongsTo
    {
        return $this->belongsTo(Airline::class);
    }

    public static function get(string $key, $default = null)
    {
        $setting = static::where('key', $key)->first();
        return $setting ? $setting->value : $default;
    }

    public function getTypedValueAttribute()
    {
        return match ($this->type) {
            'float' => (float) $this->value,
            'integer' => (int) $this->value,
            'boolean' => (bool) $this->value,
            default => $this->value,
        };
    }
}
