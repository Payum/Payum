<?php
namespace Payum\Core\Bridge\Symfony;

use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\CoreGatewayFactory;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use Symfony\Component\DependencyInjection\ContainerInterface;

class ContainerAwareCoreGatewayFactory extends CoreGatewayFactory implements ContainerAwareInterface
{
    use ContainerAwareTrait;

    /**
     * @param ArrayObject $config
     */
    protected function buildClosures(ArrayObject $config)
    {
        foreach ($config as $name => $value) {
            if (false == $value || false == is_string($value)) {
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
