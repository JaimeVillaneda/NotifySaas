<?php

namespace App\Services;

use App\Models\MessageDispatch;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class WhatsAppDeliveryService
{
    /**
     * Envia mensajes de WhatsApp mediante Evolution API.
     * Devuelve el identificador aceptado por la API.
     */
    public function send(MessageDispatch $dispatch): string
    {
        $dispatch->loadMissing([
            'company',
            'person',
            'connection',
        ]);

        $company = $dispatch->company;
        $person = $dispatch->person;
        $connection = $dispatch->connection;

        // Verificar los registros necesarios.
        if (! $company || ! $person || ! $connection) {
            throw new RuntimeException(
                'Faltan datos para procesar el envio.'
            );
        }

        // Impedir envios desde empresas o clientes inactivos.
        if (! $company->is_active || ! $person->is_active) {
            throw new RuntimeException(
                'La empresa o el cliente esta inactivo.'
            );
        }

        // Verificar canal y conexion.
        if (
            $dispatch->channel !== 'whatsapp' ||
            $connection->channel !== 'whatsapp' ||
            ! $connection->is_enabled
        ) {
            throw new RuntimeException(
                'La conexion de WhatsApp no es valida.'
            );
        }

        // Proteger el aislamiento entre empresas.
        if (
            (int) $dispatch->company_id !== (int) $person->company_id ||
            (int) $dispatch->company_id !== (int) $connection->company_id ||
            (int) $dispatch->company_id !== (int) $company->id
        ) {
            throw new RuntimeException(
                'Los registros pertenecen a empresas diferentes.'
            );
        }

        // Verificar la configuracion de Evolution API.
        if (
            blank($connection->base_url) ||
            blank($connection->instance_name) ||
            blank($connection->secret)
        ) {
            throw new RuntimeException(
                'La configuracion de Evolution API esta incompleta.'
            );
        }

        // Numero internacional sin espacios ni simbolos.
        $number = preg_replace('/\D+/', '', (string) $person->phone);

        // Para pruebas con celulares colombianos de 10 digitos.
        if (preg_match('/^3\d{9}$/', $number)) {
            $number = '57' . $number;
        }

        if (! preg_match('/^[1-9]\d{7,14}$/', $number)) {
            throw new RuntimeException(
                'El numero de WhatsApp no tiene un formato valido.'
            );
        }

        // Construir el endpoint de envio.
        $url = rtrim($connection->base_url, '/')
            . '/message/sendText/'
            . rawurlencode($connection->instance_name);

        // Enviar el mensaje de texto.
        $response = Http::timeout(20)
            ->acceptJson()
            ->asJson()
            ->withHeaders([
                'apikey' => $connection->secret,
            ])
            ->post($url, [
                'number' => $number,
                'text' => (string) $dispatch->message,
            ]);

        // Verificar que Evolution API acepto la solicitud.
        if (! $response->successful()) {
            throw new RuntimeException(
                'Evolution API rechazo el envio. HTTP '
                . $response->status()
            );
        }

        $messageId = $response->json('key.id');

        if (! is_string($messageId) || blank($messageId)) {
            throw new RuntimeException(
                'Evolution API no devolvio un identificador de mensaje.'
            );
        }

        return $messageId;
    }
}
