<?php
namespace Payum\Bundle\PayumBundle\Registry;

use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

use Payum\Registry\AbstractRegistry;

class ContainerAwareRegistry extends AbstractRegistry implements ContainerAwareInterface 
{
    /**
     * @var ContainerInterface
     */
    protected $container;
    
    /**
     * {@inheritdoc}
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    /**
     * {@inheritdoc}
     */
    protected function getService($id)
    {
        return $this->container->get($id);
    }
}