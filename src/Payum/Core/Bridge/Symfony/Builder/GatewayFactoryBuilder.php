<?php

namespace Payum\Core\Bridge\Symfony\Builder;

use Payum\Core\GatewayFactoryInterface;

class GatewayFactoryBuilder
{
    private string $gatewayFactoryClass;

    /**
     * @param string $gatewayFactoryClass
     */
    public function __construct($gatewayFactoryClass)
    {
        $this->gatewayFactoryClass = $gatewayFactoryClass;
    }

    public function __invoke()
    {
        return call_user_func_array([$this, 'build'], func_get_args());
    }

    public function build(array $defaultConfig, GatewayFactoryInterface $coreGatewayFactory): object
    {
        $gatewayFactoryClass = $this->gatewayFactoryClass;

        return new $gatewayFactoryClass($defaultConfig, $coreGatewayFactory);
    }
}
