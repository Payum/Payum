<?php

declare(strict_types=1);

namespace Payum\Core\Config;

use Payum\Core\Gateway\GatewayInterface;

/**
 * A gateway's configuration: credentials and the switches that change how it behaves.
 *
 * Implementations should be immutable value objects that validate in their constructor, so that a missing
 * or malformed credential fails at boot with a stack trace pointing at the application's own wiring,
 * rather than deep inside a handler at capture time.
 *
 * Core registers the instance in the gateway's container under both its own class and this interface,
 * which is what lets an Api type-hint the concrete config and be autowired with no definition.
 */
interface GatewayConfig
{
    /**
     * The gateway this config configures.
     *
     * This is the link PayumBuilder follows to go from a config the application handed it to the gateway
     * type it describes. {@see GatewayInterface::configClass()} is the same edge in the other direction,
     * for an admin UI that has a gateway but no config yet; a conformance test asserts the two agree.
     *
     * @return class-string<GatewayInterface>
     */
    public function getGatewayClass(): string;
}
