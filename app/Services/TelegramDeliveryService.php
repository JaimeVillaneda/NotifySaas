<?php

namespace App\Services;

use App\Models\MessageDispatch;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class TelegramDeliveryService
{
    /**
     * Envia un recordatorio mediante Telegram Bot API.
     *
     * Devuelve el ID del mensaje aceptado por Telegram.
     */
    public function send(MessageDispatch $dispatch): int
    {
        // Cargar el cliente y la conexion correspondiente.
        $dispatch->loadMissing([
            'person',
            'connection',
        ]);

        $person = $dispatch->person;
        $connection = $dispatch->connection;

        // Verificar que existan los registros requeridos.
        if (! $person || ! $connection) {
            throw new RuntimeException(
                'No existe el cliente o la conexion.'
            );
        }

        // Verificar que el canal sea Telegram.
        if (
            $dispatch->channel !== 'telegram' ||
            $connection->channel !== 'telegram'
        ) {
            throw new RuntimeException(
                'El canal configurado no es Telegram.'
            );
        }

        // No enviar utilizando conexiones deshabilitadas.
        if (! $connection->is_enabled) {
            throw new RuntimeException(
                'La conexion de Telegram esta deshabilitada.'
            );
        }

        // No enviar a clientes desactivados.
        if (! $person->is_active) {
            throw new RuntimeException(
                'El cliente se encuentra inactivo.'
            );
        }

        // US-03: Validar aislamiento entre empresas.
        if (
            ! $dispatch->company_id ||
            (int) $dispatch->company_id !==
                (int) $person->company_id ||
            (int) $dispatch->company_id !==
                (int) $connection->company_id
        ) {
            throw new RuntimeException(
                'Los registros pertenecen a empresas diferentes.'
            );
        }

        // Verificar que exista el token del bot.
        if (blank($connection->secret)) {
            throw new RuntimeException(
                'Falta configurar el token de Telegram.'
            );
        }

        // Cada destinatario necesita su propio Chat ID.
        if (blank($person->telegram_chat_id)) {
            throw new RuntimeException(
                'El cliente no tiene Telegram Chat ID.'
            );
        }

        // Enviar el mensaje a Telegram.
        $response = Http::timeout(15)
            ->acceptJson()
            ->asJson()
            ->post(
                'https://api.telegram.org/bot'
                    . $connection->secret
                    . '/sendMessage',
                [
                    'chat_id' => (string) $person->telegram_chat_id,
                    'text' => (string) $dispatch->message,
                    'disable_web_page_preview' => true,
                ]
            );

        // Comprobar si Telegram acepto la solicitud.
        if (
            ! $response->successful() ||
            $response->json('ok') !== true
        ) {
            throw new RuntimeException(
                'Telegram rechazo el envio. HTTP '
                    . $response->status()
            );
        }

        $messageId = $response->json('result.message_id');

        if (! is_numeric($messageId)) {
            throw new RuntimeException(
                'Telegram no devolvio un ID de mensaje valido.'
            );
        }

        return (int) $messageId;
    }
}
