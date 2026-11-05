<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

/**
 * NotifySaaS - Programacion automatica.
 *
 * PASO 1:
 * Buscar recordatorios que deben ejecutarse.
 * Genera los registros de envio pendientes.
 */
Schedule::command('reminders:process')
    ->everyMinute()
    ->withoutOverlapping(10);

/**
 * PASO 2:
 * Procesar los mensajes pendientes.
 *
 * Actualmente soporta Email y Telegram.
 * Actualiza el historial segun el resultado.
 */
Schedule::command('messages:send')
    ->everyMinute()
    ->withoutOverlapping(10);
