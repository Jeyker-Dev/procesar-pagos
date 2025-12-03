<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Exception;
use App\ConsumeExternalServices;

class CurrencyConverterService
{
    use ConsumeExternalServices;

    protected string $baseUri = '';
    protected string $accessKey = '';

    public function __construct()
    {
        $this->baseUri = config('services.exchangerate.base_uri');
        $this->accessKey = config('services.exchangerate.access_key');
    }

    public function convert(string $from, string $to, float $amount): float
    {
        $key = "exrate_convert_{$from}_{$to}_{$amount}";

        return Cache::remember($key, now()->addHour(), function () use ($from, $to, $amount) {
            $convertion = [
                'from' => strtoupper($from),
                'to' => strtoupper($to),
                'amount' => $amount,
                'access_key' => $this->accessKey,
            ];

            $response = Http::timeout(5)->get("{$this->baseUri}/convert", $convertion);

            if (! $response->ok() || ! isset($response['result'])) {
                throw new Exception('Error al obtener tasa de conversión');
            }

            return round((float) $response['result'], 2);
        });
    }

    public function resolveAuthorization(&$queryParams, &$formParams, &$headers): void
    {
        $queryParams['access_key'] = $this->resolveAccessToken();
    }

    public function resolveAccessToken(): string
    {
        return $this->accessKey;
    }
}
