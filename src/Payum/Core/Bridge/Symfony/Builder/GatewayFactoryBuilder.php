<?php

namespace Payum\Core\Bridge\Symfony\Builder;

use Payum\Core\GatewayFactoryInterface;

@trigger_error('The '.__NAMESPACE__.'\GatewayFactoryBuilder class is deprecated since version 2.0 and will be removed in 3.0. Use the same class from Payum/PayumBundle instead.', E_USER_DEPRECATED);

/**
 * @deprecated since 2.0. Use the same class from Payum/PayumBundle instead.
 */
class GatewayFactoryBuilder
{
    /**
     * @var string
     */
    private $gatewayFactoryClass;

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

    /**
     * @return GatewayFactoryInterface
     */
    public function build(array $defaultConfig, GatewayFactoryInterface $coreGatewayFactory)
    {
        $gatewayFactoryClass = $this->gatewayFactoryClass;

        return new $gatewayFactoryClass($defaultConfig, $coreGatewayFactory);
    }
}
