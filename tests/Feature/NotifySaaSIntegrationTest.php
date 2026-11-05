<?php

use App\Models\Company;
use App\Models\Person;
use App\Models\MessageTemplate;
use App\Models\MessageAutomation;
use App\Models\MessageDispatch;
use App\Models\CommunicationConnection;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Http;

it('procesa un recordatorio hasta registrar su envio exitoso', function () {

    // PASO 1: Crear una empresa.
    $empresa = Company::create([
        'name' => 'Empresa Integracion NotifySaaS',
        'is_active' => true,
    ]);

    // PASO 2: Registrar un cliente.
    $cliente = Person::create([
        'company_id' => $empresa->id,
        'first_name' => 'Carlos',
        'last_name' => 'Prueba',
        'phone' => '3001234567',
        'telegram_chat_id' => '123456789',
        'is_active' => true,
    ]);

    // PASO 3: Crear una plantilla de mensaje.
    $plantilla = MessageTemplate::create([
        'company_id' => $empresa->id,
        'name' => 'Plantilla Integracion',
        'body' => 'Hola {{ nombre }}, tienes un recordatorio.',
        'is_active' => true,
    ]);

    // PASO 4: Crear conexion de Telegram.
    $conexion = CommunicationConnection::create([
        'company_id' => $empresa->id,
        'name' => 'Telegram Integracion',
        'channel' => 'telegram',
        'secret' => 'TOKEN-SIMULADO',
        'is_enabled' => true,
    ]);

    // PASO 5: Programar un recordatorio.
    $recordatorio = MessageAutomation::create([
        'company_id' => $empresa->id,
        'person_id' => $cliente->id,
        'message_template_id' => $plantilla->id,
        'communication_connection_id' => $conexion->id,
        'name' => 'Recordatorio Integral',
        'channel' => 'telegram',
        'frequency' => 'once',
        'is_active' => true,
        'status' => 'pending',
        'next_run_at' => now()->subMinute(),
    ]);

    // PASO 6: Simular la respuesta de Telegram.
    // No se enviara ningun mensaje real.
    Http::preventStrayRequests();

    Http::fake([
        'api.telegram.org/*' => Http::response([
            'ok' => true,
            'result' => [
                'message_id' => 987,
            ],
        ], 200),
    ]);

    // PASO 7: Procesar recordatorios pendientes.
    expect(
        Artisan::call('reminders:process')
    )->toBe(0);

    // Debe generarse exactamente un envio.
    expect(MessageDispatch::count())->toBe(1);

    $envio = MessageDispatch::firstOrFail();

    // Verificar que el mensaje quedo preparado.
    expect($envio->status)->toBe('pending');

    expect($envio->message)
        ->toBe(
            'Hola Carlos Prueba, tienes un recordatorio.'
        );

    expect($envio->company_id)
        ->toBe($empresa->id);

    // PASO 8: Ejecutar el servicio de envio.
    expect(
        Artisan::call('messages:send')
    )->toBe(0);

    // PASO 9: Verificar el historial.
    $envio->refresh();

    expect($envio->status)->toBe('sent');

    expect($envio->attempts)->toBe(1);

    expect($envio->sent_at)->not->toBeNull();

    expect($envio->response)->toContain('987');

    // El recordatorio de una sola ejecucion
    // debe haber quedado completado.
    expect($recordatorio->fresh()->status)
        ->toBe('completed');

    // PASO 10: Verificar el destino del mensaje.
    Http::assertSent(function ($request) {

        return $request['chat_id'] === '123456789'
            && $request['text'] ===
                'Hola Carlos Prueba, tienes un recordatorio.';
    });

    Http::assertSentCount(1);

    // PASO 11: Ejecutar nuevamente ambos comandos.
    // No debe generar ni reenviar mensajes.
    expect(
        Artisan::call('reminders:process')
    )->toBe(0);

    expect(
        Artisan::call('messages:send')
    )->toBe(0);

    expect(MessageDispatch::count())->toBe(1);

    Http::assertSentCount(1);
});
