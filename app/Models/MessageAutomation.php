<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MessageAutomation extends Model
{
    protected $fillable = [
        'message_template_id',
        'name',
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

    public function template(): BelongsTo
    {
        return $this->belongsTo(MessageTemplate::class, 'message_template_id');
    }

    public function dispatches(): HasMany
    {
        return $this->hasMany(MessageDispatch::class);
    }
}
