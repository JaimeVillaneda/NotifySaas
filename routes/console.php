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

    
/**
 * PASO 3:
 * Detectar mensajes que quedaron interrumpidos.
 *
 * Se revisan cada 5 minutos.
 * El comando solo marca para revision los mensajes
 * que llevan mas de 30 minutos en processing.
 *
 * No reenvia mensajes automaticamente.
 */
Schedule::command('messages:recover')
    ->everyFiveMinutes()
    ->withoutOverlapping(10);
