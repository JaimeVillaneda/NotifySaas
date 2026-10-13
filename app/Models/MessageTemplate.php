<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MessageTemplate extends Model
{
    protected $fillable = ['name', 'slug', 'body', 'is_active', 'description'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    public function automations(): HasMany
    {
        return $this->hasMany(MessageAutomation::class);
    }
}
