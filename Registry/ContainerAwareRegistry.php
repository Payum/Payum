<?php
namespace Payum\Bundle\PayumBundle\Registry;

use Payum\Core\Registry\AbstractRegistry;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class ContainerAwareRegistry extends AbstractRegistry implements ContainerAwareInterface
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * {@inheritDoc}
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    /**
     * {@inheritDoc}
     */
    protected function getService($id)
    {
        return $this->container->get($id);
    }
}