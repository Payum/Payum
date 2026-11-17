<?php

declare(strict_types=1);

namespace Payum\Core\Exception;

use Throwable;

/**
 * A stored gateway config exists but cannot be built: its type is not registered, or its options do not
 * construct the gateway's config.
 *
 * Not an InvalidArgumentException, so a FallbackRegistry reports it instead of looking the name up elsewhere.
 */
class GatewayUnavailableException extends RuntimeException
{
    public static function unknownType(string $gatewayName, mixed $type): self
    {
        return new self(sprintf(
            'Gateway "%s" is stored with type %s, which is not registered. Register it with Payum\Core\PayumBuilder::addGatewayType().',
            $gatewayName,
            is_string($type) ? '"' . $type . '"' : 'none',
        ));
    }

    public static function invalidConfig(string $gatewayName, string $configClass, Throwable $previous): self
    {
        return new self(sprintf(
            'Gateway "%s" is stored with options that do not construct %s: %s',
            $gatewayName,
            $configClass,
            $previous->getMessage(),
        ), 0, $previous);
    }
}
