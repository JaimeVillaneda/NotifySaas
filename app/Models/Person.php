<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Person extends Model
{
    /**
     * Datos administrables del cliente/destinatario.
     */
    protected $fillable = [
        'first_name',
        'company_id',
        'last_name',
        'phone',
        'email',
        'preferred_channel',
        'telegram_chat_id',
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

    /**
     * Historial de envios dirigidos a esta persona.
     */
    public function dispatches(): HasMany
    {
        return $this->hasMany(MessageDispatch::class);
    }

    /**
     * Empresa a la que pertenece la persona.
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Nombre completo calculado para mostrarlo en tablas y selectores.
     */
    public function getFullNameAttribute(): string
    {
        return trim($this->first_name.' '.$this->last_name);
    }
}
