<?php

namespace App\Services;

use App\Models\CommunicationConnection;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;

class EvolutionApiService
{
    /**
     * Envia un mensaje de texto usando una conexion WhatsApp Evolution API.
     *
     * Si no recibe una conexion especifica, usa la ultima conexion WhatsApp
     * activa registrada en el modulo de Conexiones.
     *
     * @return array<string, mixed>
     */
    public function sendText(string $phone, string $message, ?CommunicationConnection $connection = null): array
    {
        $connection ??= CommunicationConnection::where('channel', 'whatsapp')
            ->where('is_enabled', true)
            ->latest()
            ->firstOrFail();

        $response = $this->client($connection)->post("/message/sendText/{$connection->instance_name}", [
            'number' => $phone,
            'text' => $message,
        ]);

        return $response->throw()->json();
    }

    /**
     * Construye el cliente HTTP con URL base, timeouts y API key cifrada.
     */
    private function client(CommunicationConnection $connection): PendingRequest
    {
        return Http::baseUrl(rtrim((string) $connection->base_url, '/'))
            ->acceptJson()
            ->asJson()
            ->connectTimeout(5)
            ->timeout(15)
            ->withHeaders(['apikey' => $connection->secret]);
    }
}
