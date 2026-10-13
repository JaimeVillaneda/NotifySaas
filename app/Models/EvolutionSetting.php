<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EvolutionSetting extends Model
{
    protected $fillable = [
        'name',
        'base_url',
        'instance_name',
        'api_key',
        'webhook_url',
        'is_active',
        'options',
    ];

    protected $hidden = ['api_key'];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'options' => 'array',
        ];
    }
}
