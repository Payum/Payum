<?php

namespace Payum\Core;

interface GatewayFactoryInterface
{
    /**
     * @return array
     */
    public function createConfig(array $config = array());

    /**
     * @return GatewayInterface
     */
    public function create(array $config = array());
}
