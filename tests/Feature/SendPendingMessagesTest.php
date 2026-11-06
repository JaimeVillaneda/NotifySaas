<?php

use App\Models\Company;
use App\Models\CommunicationConnection;
use App\Models\MessageDispatch;
use App\Models\Person;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Http;

it('registra los envios exitosos y fallidos de Telegram', function () {

    // PASO 1: Crear una empresa de prueba.
    $empresa = Company::create([
        'name' => 'Empresa Pruebas de Envio',
        'is_active' => true,
    ]);

    // PASO 2: Crear una conexion Telegram.
    $conexion = CommunicationConnection::create([
        'company_id' => $empresa->id,
        'name' => 'Telegram Prueba',
        'channel' => 'telegram',
        'secret' => 'TOKEN-SIMULADO',
        'is_enabled' => true,
    ]);

    // PASO 3: Crear dos destinatarios.
    $clienteA = Person::create([
        'company_id' => $empresa->id,
        'first_name' => 'Cliente Exitoso',
        'phone' => '3001111111',
        'telegram_chat_id' => '111111',
        'is_active' => true,
    ]);

    $clienteB = Person::create([
        'company_id' => $empresa->id,
        'first_name' => 'Cliente Fallido',
        'phone' => '3002222222',
        'telegram_chat_id' => '222222',
        'is_active' => true,
    ]);

    // PASO 4: Crear dos envios pendientes.
    $envioA = MessageDispatch::create([
        'company_id' => $empresa->id,
        'person_id' => $clienteA->id,
        'communication_connection_id' => $conexion->id,
        'phone' => $clienteA->phone,
        'channel' => 'telegram',
        'message' => 'Mensaje exitoso',
        'status' => 'pending',
        'scheduled_at' => now()->subMinute(),
    ]);

    $envioB = MessageDispatch::create([
        'company_id' => $empresa->id,
        'person_id' => $clienteB->id,
        'communication_connection_id' => $conexion->id,
        'phone' => $clienteB->phone,
        'channel' => 'telegram',
        'message' => 'Mensaje fallido',
        'status' => 'pending',
        'scheduled_at' => now()->subMinute(),
    ]);

    // PASO 5: Simular las respuestas de Telegram.
    // No se realizan solicitudes reales.
    Http::preventStrayRequests();

    Http::fake(function (Request $request) {

        if ($request['text'] === 'Mensaje exitoso') {
            return Http::response([
                'ok' => true,
                'result' => [
                    'message_id' => 789,
                ],
            ], 200);
        }

        return Http::response([
            'ok' => false,
            'description' => 'Error simulado',
        ], 400);
    });

    // PASO 6: Ejecutar el comando de envios.
    $resultado = Artisan::call('messages:send');

    expect($resultado)->toBe(0);

    // PASO 7: Verificar el envio exitoso.
    $envioA->refresh();

    expect($envioA->status)->toBe('sent');
    expect($envioA->attempts)->toBe(1);
    expect($envioA->sent_at)->not->toBeNull();

    expect($envioA->response)
        ->toContain('789');

    // PASO 8: Verificar el envio fallido.
    $envioB->refresh();

    expect($envioB->status)->toBe('failed');
    expect($envioB->attempts)->toBe(1);
    expect($envioB->sent_at)->toBeNull();
    expect($envioB->error)->not->toBeNull();

    // PASO 9: Comprobar las solicitudes simuladas.
    Http::assertSentCount(2);
});


it('procesa correos exitosos y fallidos', function () {

    // Crear empresa de prueba.
    $empresa = Company::create([
        'name' => 'Empresa Prueba Email',
        'is_active' => true,
    ]);

    // Crear conexion de correo.
    $conexion = CommunicationConnection::create([
        'company_id' => $empresa->id,
        'name' => 'SMTP Prueba',
        'channel' => 'email',
        'is_enabled' => true,
    ]);

    // Crear primer cliente.
    $clienteA = Person::create([
        'company_id' => $empresa->id,
        'first_name' => 'Cliente Email A',
        'phone' => '3004440001',
        'email' => 'clientea@example.com',
        'is_active' => true,
    ]);

    // Crear segundo cliente.
    $clienteB = Person::create([
        'company_id' => $empresa->id,
        'first_name' => 'Cliente Email B',
        'phone' => '3004440002',
        'email' => 'clienteb@example.com',
        'is_active' => true,
    ]);

    // Crear correo que sera exitoso.
    $envioA = MessageDispatch::create([
        'company_id' => $empresa->id,
        'person_id' => $clienteA->id,
        'communication_connection_id' => $conexion->id,
        'phone' => $clienteA->phone,
        'channel' => 'email',
        'message' => 'Correo exitoso',
        'status' => 'pending',
        'scheduled_at' => now()->subMinute(),
    ]);

    // Crear correo que fallara.
    $envioB = MessageDispatch::create([
        'company_id' => $empresa->id,
        'person_id' => $clienteB->id,
        'communication_connection_id' => $conexion->id,
        'phone' => $clienteB->phone,
        'channel' => 'email',
        'message' => 'Correo fallido',
        'status' => 'pending',
        'scheduled_at' => now()->subMinute(),
    ]);

    /**
     * Simular el servicio SMTP.
     *
     * No se enviaran correos reales.
     */
    $emailSimulado = Mockery::mock(
        \App\Services\EmailDeliveryService::class
    );

    $emailSimulado
        ->shouldReceive('send')
        ->twice()
        ->andReturnUsing(
            function (MessageDispatch $dispatch): void {

                // Simular un fallo en el segundo correo.
                if ($dispatch->message === 'Correo fallido') {
                    throw new \RuntimeException(
                        'Error SMTP simulado'
                    );
                }
            }
        );

    // Sustituir el servicio real por el simulado.
    app()->instance(
        \App\Services\EmailDeliveryService::class,
        $emailSimulado
    );

    // Ejecutar el comando.
    $resultado = Artisan::call('messages:send');

    expect($resultado)->toBe(0);

    // Verificar correo exitoso.
    $envioA->refresh();

    expect($envioA->status)->toBe('sent');
    expect($envioA->attempts)->toBe(1);
    expect($envioA->sent_at)->not->toBeNull();
    expect($envioA->response)->toContain('SMTP');

    // Verificar correo fallido.
    $envioB->refresh();

    expect($envioB->status)->toBe('failed');
    expect($envioB->attempts)->toBe(1);
    expect($envioB->sent_at)->toBeNull();
    expect($envioB->error)->not->toBeNull();

    // Confirmar que existen ambos registros.
    expect(MessageDispatch::count())->toBe(2);
});


it('procesa envios exitosos y fallidos de WhatsApp', function () {

    // 1. Empresa de prueba.
    $empresa = Company::create([
        'name' => 'Empresa WhatsApp Procesador',
        'is_active' => true,
    ]);

    // 2. Conexion ficticia de Evolution API.
    $conexion = CommunicationConnection::create([
        'company_id' => $empresa->id,
        'name' => 'WhatsApp Simulado',
        'channel' => 'whatsapp',
        'is_enabled' => true,
        'base_url' => 'http://127.0.0.1:8080',
        'instance_name' => 'notifysaas',
        'secret' => 'CLAVE-SIMULADA',
    ]);

    // 3. Cliente de prueba.
    $cliente = Person::create([
        'company_id' => $empresa->id,
        'first_name' => 'Cliente',
        'last_name' => 'WhatsApp',
        'phone' => '3001234567',
        'is_active' => true,
    ]);

    // 4. Dos mensajes pendientes.
    $exitoso = MessageDispatch::create([
        'company_id' => $empresa->id,
        'person_id' => $cliente->id,
        'communication_connection_id' => $conexion->id,
        'phone' => $cliente->phone,
        'channel' => 'whatsapp',
        'message' => 'WhatsApp exitoso',
        'status' => 'pending',
        'attempts' => 0,
        'scheduled_at' => now()->subMinute(),
    ]);

    $fallido = MessageDispatch::create([
        'company_id' => $empresa->id,
        'person_id' => $cliente->id,
        'communication_connection_id' => $conexion->id,
        'phone' => $cliente->phone,
        'channel' => 'whatsapp',
        'message' => 'WhatsApp fallido',
        'status' => 'pending',
        'attempts' => 0,
        'scheduled_at' => now()->subMinute(),
    ]);

    // 5. Simular respuestas de Evolution API.
    // No se realizaran llamadas reales.
    Http::preventStrayRequests();

    Http::fake(function (Request $request) {

        if ($request['text'] === 'WhatsApp exitoso') {
            return Http::response([
                'key' => [
                    'id' => 'WA-TEST-001',
                ],
            ], 201);
        }

        return Http::response([
            'message' => 'Error simulado',
        ], 400);
    });

    // 6. Ejecutar el comando dentro del entorno de prueba.
    $resultado = Artisan::call('messages:send');

    expect($resultado)->toBe(0);

    // 7. Comprobar envio exitoso.
    $exitoso->refresh();

    expect($exitoso->status)->toBe('sent');
    expect($exitoso->attempts)->toBe(1);
    expect($exitoso->sent_at)->not->toBeNull();
    expect($exitoso->response)->toContain('WA-TEST-001');

    // 8. Comprobar envio fallido.
    $fallido->refresh();

    expect($fallido->status)->toBe('failed');
    expect($fallido->attempts)->toBe(1);
    expect($fallido->sent_at)->toBeNull();
    expect($fallido->error)->not->toBeNull();

    // 9. Ambas solicitudes fueron simuladas.
    Http::assertSentCount(2);
});
