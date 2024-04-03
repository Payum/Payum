<?php

namespace Payum\Core\Bridge\Symfony\Builder;

use Payum\Core\Bridge\Symfony\ContainerAwareCoreGatewayFactory;
use Payum\Core\GatewayFactoryInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

@trigger_error('The '.__NAMESPACE__.'\CoreGatewayFactoryBuilder class is deprecated since version 2.0 and will be removed in 3.0. Use the same class from Payum/PayumBundle instead.', E_USER_DEPRECATED);

/**
 * @deprecated since 2.0. Use the same class from Payum/PayumBundle instead.
 */
class CoreGatewayFactoryBuilder implements ContainerAwareInterface
{
    use ContainerAwareTrait;

    public function __invoke()
    {
        return call_user_func_array([$this, 'build'], func_get_args());
    }

    /**
     * @return GatewayFactoryInterface
     */
    public function build(array $defaultConfig)
    {
        $coreGatewayFactory = new ContainerAwareCoreGatewayFactory($defaultConfig);
        $coreGatewayFactory->setContainer($this->container);

        return $coreGatewayFactory;
    }
}
