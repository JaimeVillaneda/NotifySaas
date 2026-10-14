<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MessageAutomation extends Model
{
    /**
     * Configuracion de un recordatorio automatizado.
     */
    protected $fillable = [
        'message_template_id',
        'company_id',
        'communication_connection_id',
        'person_id',
        'name',
        'channel',
        'frequency',
        'send_time',
        'start_date',
        'end_date',
        'interval_value',
        'interval_unit',
        'week_days',
        'next_run_at',
        'last_run_at',
        'is_active',
        'status',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'week_days' => 'array',
            'next_run_at' => 'datetime',
            'last_run_at' => 'datetime',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Plantilla que define el cuerpo del mensaje.
     */
    public function template(): BelongsTo
    {
        return $this->belongsTo(MessageTemplate::class, 'message_template_id');
    }

    /**
     * Empresa propietaria del recordatorio.
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Conexion externa usada para enviar el recordatorio.
     */
    public function connection(): BelongsTo
    {
        return $this->belongsTo(CommunicationConnection::class, 'communication_connection_id');
    }

    /**
     * Cliente especifico destinatario. Si es null, aplica a todos los clientes activos de la empresa.
     */
    public function person(): BelongsTo
    {
        return $this->belongsTo(Person::class);
    }

    /**
     * Envios generados por este recordatorio.
     */
    public function dispatches(): HasMany
    {
        return $this->hasMany(MessageDispatch::class);
    }
}
