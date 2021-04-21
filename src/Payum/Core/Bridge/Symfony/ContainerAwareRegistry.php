<?php
namespace Payum\Core\Bridge\Symfony;

use Payum\Core\Registry\AbstractRegistry;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

class ContainerAwareRegistry extends AbstractRegistry implements ContainerAwareInterface
{
    use ContainerAwareTrait;

    /**
     * {@inheritDoc}
     */
    protected function getService($id)
    {
        return $this->container->get($id);
    }
}
