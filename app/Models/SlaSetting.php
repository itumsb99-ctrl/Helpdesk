<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SlaSetting extends Model
{
    protected $fillable = [
        'priority',
        'response_minutes',
        'resolution_minutes',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'response_minutes' => 'integer',
            'resolution_minutes' => 'integer',
            'is_active' => 'boolean',
        ];
    }
}
