<?php

declare(strict_types = 1);

namespace App\Services\Gateways;

use App\Exceptions\GatewayException;
use Illuminate\Support\Facades\Http;

class Gateway1Service implements GatewayInterface
{
    private string $endpoint;

    private string $token;

    public function __construct()
    {
        $credentials = config('services.gateways.gateway_1');

        $this->endpoint = $credentials['endpoint'];
        $this->token    = $this->authenticate($credentials['endpoint'], [
            'email' => $credentials['email'],
            'token' => $credentials['token'],
        ]);
    }

    public function charge(array $payload): array
    {
        $response = Http::withToken($this->token)
            ->acceptJson()
            ->baseUrl($this->endpoint)
            ->post('transactions', $payload);

        if ($response->failed()) {
            throw GatewayException::chargeFailed('gateway_1');
        }

        return $response->json();
    }

    public function refund(string $transactionId): array
    {
        $response = Http::withToken($this->token)
            ->acceptJson()
            ->baseUrl($this->endpoint)
            ->post("transactions/{$transactionId}/charge_back");

        if ($response->failed()) {
            throw GatewayException::refundFailed('gateway_1');
        }

        return $response->json();
    }

    private function authenticate(string $endpoint, array $payload): string
    {
        $response = Http::baseUrl($endpoint)
            ->acceptJson()
            ->post('login', $payload);

        if ($response->failed()) {
            throw GatewayException::authFailed('gateway_1');
        }

        return $response->json('token');
    }
}
