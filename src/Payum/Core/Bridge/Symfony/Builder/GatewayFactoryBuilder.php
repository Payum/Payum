<?php

namespace Payum\Core\Bridge\Symfony\Builder;

use Payum\Core\GatewayFactoryInterface;
use function trigger_deprecation;

trigger_deprecation('payum/core', '2.0.0', 'The %s class is deprecated and will be removed in 3.0. Use Payum\Bundle\PayumBundle\Builder\GatewayFactoryBuilder from payum/payum-bundle instead.', GatewayFactoryBuilder::class);

/**
 * @deprecated since 2.0.0, will be removed in 3.0. Use Payum\Bundle\PayumBundle\Builder\GatewayFactoryBuilder from payum/payum-bundle instead.
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
        return call_user_func_array($this->build(...), func_get_args());
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
