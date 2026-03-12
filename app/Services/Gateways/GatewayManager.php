<?php

declare(strict_types = 1);

namespace App\Services\Gateways;

use App\Exceptions\GatewayException;
use App\Models\Gateway;
use Illuminate\Database\Eloquent\Collection;

class GatewayManager
{
    private array $map = [
        'gateway_1' => Gateway1Service::class,
        'gateway_2' => Gateway2Service::class,
    ];

    public function process(array $payload): array
    {
        foreach ($this->availableGateways() as $gateway) {
            try {
                return $this->resolve($gateway->name)->charge($payload);
            } catch (GatewayException) {
                continue;
            }
        }

        throw GatewayException::allGatewaysFailed();
    }

    public function refund(string $externalId, string $gatewayName): array
    {
        return $this->resolve($gatewayName)->refund($externalId);
    }

    private function resolve(string $name): GatewayInterface
    {
        $class = $this->map[$name] ?? null;

        if (!$class) {
            throw GatewayException::authFailed($name);
        }

        return new $class();
    }

    private function availableGateways(): Collection
    {
        return Gateway::available()->orderBy('priority')->get();
    }
}
