<?php

use App\Models\Company;
use App\Models\CommunicationConnection;
use App\Models\MessageDispatch;
use App\Models\Person;
use App\Services\WhatsAppDeliveryService;
use Illuminate\Support\Facades\Http;

// PRUEBA 1: envio exitoso simulado.
it('envia un mensaje de WhatsApp mediante Evolution API', function () {

    $empresa = Company::create([
        'name' => 'Empresa WhatsApp Prueba',
        'is_active' => true,
    ]);

    $cliente = Person::create([
        'company_id' => $empresa->id,
        'first_name' => 'Carlos',
        'last_name' => 'WhatsApp',
        'phone' => '3001234567',
        'is_active' => true,
    ]);

    $conexion = CommunicationConnection::create([
        'company_id' => $empresa->id,
        'name' => 'Evolution API Prueba',
        'channel' => 'whatsapp',
        'is_enabled' => true,
        'base_url' => 'http://127.0.0.1:8080',
        'instance_name' => 'notifysaas',
        'secret' => 'CLAVE-SIMULADA',
    ]);

    $envio = MessageDispatch::create([
        'company_id' => $empresa->id,
        'person_id' => $cliente->id,
        'communication_connection_id' => $conexion->id,
        'phone' => $cliente->phone,
        'channel' => 'whatsapp',
        'message' => 'Hola Carlos, este es un recordatorio.',
        'status' => 'pending',
    ]);

    // Prohibir llamadas reales durante la prueba.
    Http::preventStrayRequests();

    // Simular Evolution API.
    Http::fake([
        'http://127.0.0.1:8080/message/sendText/notifysaas'
            => Http::response([
                'key' => [
                    'id' => 'WHATSAPP-TEST-001',
                ],
            ], 201),
    ]);

    $messageId = app(
        WhatsAppDeliveryService::class
    )->send($envio);

    expect($messageId)->toBe('WHATSAPP-TEST-001');

    // Verificar numero, texto, endpoint y API Key.
    Http::assertSent(function ($request) {
        return $request->method() === 'POST'
            && $request->url() ===
                'http://127.0.0.1:8080/message/sendText/notifysaas'
            && $request->hasHeader(
                'apikey',
                'CLAVE-SIMULADA'
            )
            && $request['number'] === '573001234567'
            && $request['text'] ===
                'Hola Carlos, este es un recordatorio.';
    });

    Http::assertSentCount(1);
});

// PRUEBA 2: aislamiento entre empresas.
it('impide enviar WhatsApp a clientes de otra empresa', function () {

    $empresaA = Company::create([
        'name' => 'Empresa WhatsApp A',
        'is_active' => true,
    ]);

    $empresaB = Company::create([
        'name' => 'Empresa WhatsApp B',
        'is_active' => true,
    ]);

    $clienteB = Person::create([
        'company_id' => $empresaB->id,
        'first_name' => 'Cliente',
        'last_name' => 'Empresa B',
        'phone' => '3005551234',
        'is_active' => true,
    ]);

    $conexionA = CommunicationConnection::create([
        'company_id' => $empresaA->id,
        'name' => 'Evolution Empresa A',
        'channel' => 'whatsapp',
        'is_enabled' => true,
        'base_url' => 'http://127.0.0.1:8080',
        'instance_name' => 'notifysaas',
        'secret' => 'CLAVE-SIMULADA',
    ]);

    // Intento incorrecto: envio de A a cliente de B.
    $envio = MessageDispatch::create([
        'company_id' => $empresaA->id,
        'person_id' => $clienteB->id,
        'communication_connection_id' => $conexionA->id,
        'phone' => $clienteB->phone,
        'channel' => 'whatsapp',
        'message' => 'Mensaje que debe bloquearse.',
        'status' => 'pending',
    ]);

    Http::preventStrayRequests();
    Http::fake();

    expect(
        fn () => app(WhatsAppDeliveryService::class)
            ->send($envio)
    )->toThrow(RuntimeException::class);

    // Ninguna solicitud debe salir a Evolution API.
    Http::assertNothingSent();
});
