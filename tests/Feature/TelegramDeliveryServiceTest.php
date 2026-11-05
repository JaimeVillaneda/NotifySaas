<?php

use App\Models\Company;
use App\Models\Person;
use App\Models\CommunicationConnection;
use App\Models\MessageDispatch;
use App\Services\TelegramDeliveryService;
use Illuminate\Support\Facades\Http;

it('envia un mensaje de Telegram correctamente', function () {

    // PASO 1: Crear una empresa de prueba.
    $empresa = Company::create([
        'name' => 'Empresa Telegram Prueba',
        'is_active' => true,
    ]);

    // PASO 2: Crear un cliente con Telegram Chat ID.
    $cliente = Person::create([
        'company_id' => $empresa->id,
        'first_name' => 'Carlos',
        'last_name' => 'Telegram',
        'phone' => '3001234567',
        'telegram_chat_id' => '123456789',
        'is_active' => true,
    ]);

    // PASO 3: Crear una conexión Telegram.
    $conexion = CommunicationConnection::create([
        'company_id' => $empresa->id,
        'name' => 'Bot Telegram Prueba',
        'channel' => 'telegram',
        'secret' => 'TOKEN-DE-PRUEBA',
        'is_enabled' => true,
    ]);

    // PASO 4: Crear un envío pendiente.
    $envio = MessageDispatch::create([
        'company_id' => $empresa->id,
        'person_id' => $cliente->id,
        'communication_connection_id' => $conexion->id,
        'phone' => $cliente->phone,
        'channel' => 'telegram',
        'message' => 'Hola Carlos, este es un recordatorio.',
        'status' => 'pending',
    ]);

    // PASO 5: Simular una respuesta exitosa.
    Http::fake([
        'api.telegram.org/*' => Http::response([
            'ok' => true,
            'result' => [
                'message_id' => 456,
            ],
        ], 200),
    ]);

    // PASO 6: Ejecutar el servicio.
    $messageId = app(
        TelegramDeliveryService::class
    )->send($envio);

    // PASO 7: Validar el identificador devuelto.
    expect($messageId)->toBe(456);

    // PASO 8: Verificar los datos enviados.
    Http::assertSent(function ($request) {

        return str_contains(
            $request->url(),
            '/botTOKEN-DE-PRUEBA/sendMessage'
        )
        && $request['chat_id'] === '123456789'
        && $request['text'] ===
            'Hola Carlos, este es un recordatorio.';
    });

    // PASO 9: Verificar que hubo una sola solicitud.
    Http::assertSentCount(1);
});
