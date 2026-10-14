<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class MessageTemplate extends Model
{
    /**
     * Campos de una plantilla reutilizable de mensaje.
     */
    protected $fillable = ['company_id', 'name', 'slug', 'body', 'is_active', 'description'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    /**
     * Recordatorios que usan esta plantilla.
     */
    public function automations(): HasMany
    {
        return $this->hasMany(MessageAutomation::class);
    }

    /**
     * Empresa propietaria de la plantilla.
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Genera automaticamente el codigo slug si el usuario no lo escribe.
     */
    protected static function booted(): void
    {
        static::saving(function (MessageTemplate $template): void {
            if (blank($template->slug)) {
                $template->slug = Str::slug($template->name);
            }
        });
    }
}
