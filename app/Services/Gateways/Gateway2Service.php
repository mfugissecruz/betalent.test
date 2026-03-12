<?php

declare(strict_types = 1);

namespace App\Services\Gateways;

use App\Exceptions\GatewayException;
use Illuminate\Support\Facades\Http;

class Gateway2Service implements GatewayInterface
{
    private string $endpoint;

    private string $token;

    private string $secret;

    public function __construct()
    {
        $credentials = config('services.gateways.gateway_2');

        $this->endpoint = $credentials['endpoint'];
        $this->token    = $credentials['token'];
        $this->secret   = $credentials['secret'];
    }

    public function charge(array $payload): array
    {
        $response = Http::withHeaders([
            'Gateway-Auth-Token'  => $this->token,
            'Gateway-Auth-Secret' => $this->secret,
        ])
            ->acceptJson()
            ->baseUrl($this->endpoint)
            ->post('transacoes', $payload);

        if ($response->failed()) {
            throw GatewayException::chargeFailed('gateway_2');
        }

        return $response->json();
    }

    public function refund(string $transactionId): array
    {
        $response = Http::withHeaders([
            'Gateway-Auth-Token'  => $this->token,
            'Gateway-Auth-Secret' => $this->secret,
        ])
            ->acceptJson()
            ->baseUrl($this->endpoint)
            ->post('transacoes/reembolso', ['id' => $transactionId]);

        if ($response->failed()) {
            throw GatewayException::refundFailed('gateway_2');
        }

        return $response->json();
    }
}
