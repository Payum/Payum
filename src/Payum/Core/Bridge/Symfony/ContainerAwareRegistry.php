<?php

namespace Payum\Core\Bridge\Symfony;

use Payum\Core\Registry\AbstractRegistry;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

/**
 * @template StorageType of object
 * @extends AbstractRegistry<StorageType>
 */
class ContainerAwareRegistry extends AbstractRegistry implements ContainerAwareInterface
{
    use ContainerAwareTrait;

    protected function getService(string $id): object
    {
        return $this->container->get($id);
    }
}
