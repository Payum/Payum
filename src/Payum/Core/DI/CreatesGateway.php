<?php

declare(strict_types=1);

namespace Payum\Core\DI;

use Payum\Core\Gateway;
use Psr\Container\ContainerInterface;

/**
 * Assembles a Gateway from a fully built container.
 *
 * A gateway factory implements this only when it genuinely needs to customise assembly.
 */
interface CreatesGateway
{
    public function createGateway(ContainerInterface $container): Gateway;
}
