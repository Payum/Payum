<?php

namespace Payum\Core;

use Payum\Core\Action\ActionInterface;
use Payum\Core\Extension\ExtensionInterface;
use Psr\Container\ContainerInterface;

interface GatewayFactoryConfigInterface
{
    public function createGateway(ContainerInterface $container): Gateway;

    /**
     * @return array<string, class-string<ActionInterface>>|list<class-string<ActionInterface>>
     */
    public function getActions(): array;

    /**
     * @return list<ExtensionInterface|class-string<ExtensionInterface>>
     */
    public function getExtensions(): array;
}
