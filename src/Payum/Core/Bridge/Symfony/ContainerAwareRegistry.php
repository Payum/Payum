<?php

namespace Payum\Core\Bridge\Symfony;

use Payum\Core\Registry\AbstractRegistry;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

@trigger_error('The '.__NAMESPACE__.'\ContainerAwareRegistry class is deprecated since version 2.0 and will be removed in 3.0. Use the same class from Payum/PayumBundle instead.', E_USER_DEPRECATED);

/**
 * @template T of object
 * @extends AbstractRegistry<T>
 *
 * @deprecated since 2.0. Use the same class from Payum/PayumBundle instead.
 * /
 */
class ContainerAwareRegistry extends AbstractRegistry implements ContainerAwareInterface
{
    use ContainerAwareTrait;

    protected function getService($id): ?object
    {
        return $this->container->get($id);
    }
}
