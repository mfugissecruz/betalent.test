<?php

declare(strict_types = 1);

namespace App\Exceptions;

use Exception;

class GatewayException extends Exception
{
    public static function authFailed(string $gateway): self
    {
        return new self("Authentication failed for gateway: {$gateway}");
    }

    public static function chargeFailed(string $gateway): self
    {
        return new self("Charge failed for gateway: {$gateway}");
    }

    public static function refundFailed(string $gateway): self
    {
        return new self("Refund failed for gateway: {$gateway}");
    }

    public static function allGatewaysFailed(): self
    {
        return new self('All gateways failed to process the request.');
    }
}
