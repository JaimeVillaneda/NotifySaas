<?php

namespace App\Services;

use App\Models\EvolutionSetting;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;

class EvolutionApiService
{
    public function sendText(string $phone, string $message, ?EvolutionSetting $setting = null): array
    {
        $setting ??= EvolutionSetting::where('is_active', true)->latest()->firstOrFail();

        $response = $this->client($setting)->post("/message/sendText/{$setting->instance_name}", [
            'number' => $phone,
            'text' => $message,
        ]);

        return $response->throw()->json();
    }

    private function client(EvolutionSetting $setting): PendingRequest
    {
        return Http::baseUrl(rtrim((string) $setting->base_url, '/'))
            ->acceptJson()
            ->asJson()
            ->withHeaders(['apikey' => $setting->api_key]);
    }
}
