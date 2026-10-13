<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MessageDispatch extends Model
{
    protected $fillable = [
        'message_automation_id',
        'message_template_id',
        'person_id',
        'phone',
        'message',
        'status',
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

    public function automation(): BelongsTo
    {
        return $this->belongsTo(MessageAutomation::class, 'message_automation_id');
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(MessageTemplate::class, 'message_template_id');
    }

    public function person(): BelongsTo
    {
        return $this->belongsTo(Person::class);
    }
}
