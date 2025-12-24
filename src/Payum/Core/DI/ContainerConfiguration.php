<?php

namespace Payum\Core\DI;

use Payum\Core\Gateway;
use Psr\Container\ContainerInterface;

interface ContainerConfiguration
{
    /**
     * @return array<string, mixed>
     */
    public function configureContainer(): array;

    public function createGateway(ContainerInterface $container): Gateway;
}
