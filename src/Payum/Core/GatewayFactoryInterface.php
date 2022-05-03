<?php
namespace Payum\Core;

interface GatewayFactoryInterface
{
    public function createConfig(array $config = []): array;

    public function create(array $config = []): GatewayInterface;
}
