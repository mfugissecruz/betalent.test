<?php

declare(strict_types = 1);

use Illuminate\Support\Facades\Log;
use Rector\Config\RectorConfig;
use Rector\Exception\Configuration\InvalidConfigurationException;

try {
    return RectorConfig::configure()
        ->withPaths([
            __DIR__ . '/app',
            __DIR__ . '/bootstrap/app.php',
            __DIR__ . '/database',
            __DIR__ . '/tests',
            __DIR__ . '/routes',
        ])
        ->withPreparedSets(
            deadCode: true,
            codeQuality: true,
            typeDeclarations: true,
            privatization: true,
            earlyReturn: true
        )
        ->withPhpSets();
} catch (InvalidConfigurationException $e) {
    Log::error($e->getMessage());
}
