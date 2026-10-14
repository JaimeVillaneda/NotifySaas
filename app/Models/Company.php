<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Company extends Model
{
    /**
     * Campos editables desde Filament para registrar empresas del SaaS.
     */
    protected $fillable = [
        'name',
        'email',
        'phone',
        'tax_id',
        'address',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    /**
     * Usuarios administradores que pertenecen a la empresa.
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    /**
     * Clientes o destinatarios registrados por la empresa.
     */
    public function people(): HasMany
    {
        return $this->hasMany(Person::class);
    }

    /**
     * Conexiones externas configuradas por canal.
     */
    public function connections(): HasMany
    {
        return $this->hasMany(CommunicationConnection::class);
    }

    /**
     * Plantillas de mensajes reutilizables de la empresa.
     */
    public function templates(): HasMany
    {
        return $this->hasMany(MessageTemplate::class);
    }

    /**
     * Recordatorios programados para la empresa.
     */
    public function automations(): HasMany
    {
        return $this->hasMany(MessageAutomation::class);
    }
}
