<?php

namespace Payum\Core\Bridge\Symfony;

use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\CoreGatewayFactory;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

@trigger_error('The ' . __NAMESPACE__ . '\ContainerAwareCoreGatewayFactory class is deprecated since version 2.0 and will be removed in 3.0. Use the same class from Payum/PayumBundle instead.', E_USER_DEPRECATED);

/**
 * @deprecated since 2.0. Use the same class from Payum/PayumBundle instead.
 */
class ContainerAwareCoreGatewayFactory extends CoreGatewayFactory implements ContainerAwareInterface
{
    use ContainerAwareTrait;

    protected function buildClosures(ArrayObject $config): void
    {
        foreach ($config as $name => $value) {
            if (! $value || ! is_string($value)) {
                continue;
            }

            $match = [];
            if (preg_match('/^%(.*?)%$/', $value, $match)) {
                $config[$name] = $value = $this->container->getParameter($match[1]);
            }

            if ('@' === $value[0] && $this->container->has(substr($value, 1))) {
                $config[$name] = $value = $this->container->get(substr($value, 1));
            }
        }

        parent::buildClosures($config);
    }
}
