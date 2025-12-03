<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Exception;

class CurrencyConverterService
{
    protected string $baseUrl = '';
    protected string $accessKey = '';

    public function __construct()
    {
        $this->baseUrl = config('services.exchangerate.base_uri');
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

            $response = Http::timeout(5)->get("{$this->baseUrl}/convert", $convertion);

            if (! $response->ok() || ! isset($response['result'])) {
                throw new Exception('Error al obtener tasa de conversión');
            }

            return round((float) $response['result'], 2);
        });
    }
}
