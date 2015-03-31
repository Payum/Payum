<?php
namespace Payum\Core;

interface GatewayFactoryInterface extends PaymentFactoryInterface
{
    /**
     * @param array $config
     *
     * @return array
     */
    public function createConfig(array $config = array());

    /**
     * @param array $config
     *
     * @return GatewayInterface
     */
    public function create(array $config = array());
}
