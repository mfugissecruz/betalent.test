<?php

declare(strict_types = 1);

namespace App\Services\Gateways;

interface GatewayInterface
{
    public function charge(array $payload): array;

    public function refund(string $transactionId): array;
}
