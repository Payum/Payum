<?php

declare(strict_types=1);

namespace Payum\Core\DI;

/**
 * Contributes service definitions to a gateway's container.
 *
 * Implemented by two quite different things:
 *
 *   - a {@see \Payum\Core\Gateway\GatewayInterface}, declaring the services it needs - an Api that
 *     cannot be autowired, a decorated handler, a second API version;
 *   - a legacy gateway factory, during migration.
 */
interface ContainerConfiguration
{
    /**
     * PHP-DI definitions, merged into the gateway's container.
     *
     * Definitions are closures resolved lazily, so this may be called on an instance that has no
     * configuration yet.
     *
     * @return array<string, mixed>
     */
    public function configureContainer(): array;
}
