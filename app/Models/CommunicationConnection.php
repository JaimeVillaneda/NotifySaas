<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CommunicationConnection extends Model
{
    /**
     * Datos de conexion para Telegram, Email SMTP o WhatsApp Evolution API.
     */
    protected $fillable = [
        'company_id',
        'name',
        'channel',
        'is_enabled',
        'host',
        'port',
        'username',
        'secret',
        'from_address',
        'from_name',
        'base_url',
        'instance_name',
        'webhook_url',
        'settings',
        'last_checked_at',
        'last_error',
    ];

    protected $hidden = ['secret'];

    protected function casts(): array
    {
        return [
            'is_enabled' => 'boolean',
            'secret' => 'encrypted',
            'settings' => 'array',
            'last_checked_at' => 'datetime',
        ];
    }

    /**
     * Empresa propietaria de la conexion.
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Recordatorios que usan esta conexion.
     */
    public function automations(): HasMany
    {
        return $this->hasMany(MessageAutomation::class);
    }

    /**
     * Historial de envios realizados o pendientes por esta conexion.
     */
    public function dispatches(): HasMany
    {
        return $this->hasMany(MessageDispatch::class);
    }
}
