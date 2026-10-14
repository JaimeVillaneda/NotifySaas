<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MessageDispatch extends Model
{
    /**
     * Registro historico de un intento de envio a un cliente.
     */
    protected $fillable = [
        'message_automation_id',
        'company_id',
        'message_template_id',
        'communication_connection_id',
        'person_id',
        'phone',
        'channel',
        'message',
        'status',
        'attempts',
        'scheduled_at',
        'sent_at',
        'response',
        'error',
    ];

    protected function casts(): array
    {
        return [
            'scheduled_at' => 'datetime',
            'sent_at' => 'datetime',
        ];
    }

    /**
     * Recordatorio que genero el envio, si aplica.
     */
    public function automation(): BelongsTo
    {
        return $this->belongsTo(MessageAutomation::class, 'message_automation_id');
    }

    /**
     * Empresa propietaria del envio.
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Conexion externa usada para este envio.
     */
    public function connection(): BelongsTo
    {
        return $this->belongsTo(CommunicationConnection::class, 'communication_connection_id');
    }

    /**
     * Plantilla usada para generar el mensaje.
     */
    public function template(): BelongsTo
    {
        return $this->belongsTo(MessageTemplate::class, 'message_template_id');
    }

    /**
     * Cliente destinatario del envio.
     */
    public function person(): BelongsTo
    {
        return $this->belongsTo(Person::class);
    }
}
