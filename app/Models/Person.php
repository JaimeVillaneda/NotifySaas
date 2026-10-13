<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Person extends Model
{
    protected $fillable = [
        'first_name',
        'last_name',
        'phone',
        'email',
        'document',
        'data',
        'is_active',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'data' => 'array',
            'is_active' => 'boolean',
        ];
    }

    public function dispatches(): HasMany
    {
        return $this->hasMany(MessageDispatch::class);
    }

    public function getFullNameAttribute(): string
    {
        return trim($this->first_name.' '.$this->last_name);
    }
}
