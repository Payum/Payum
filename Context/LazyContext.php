<?php
namespace Payum\Bundle\PayumBundle\Context;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\ContainerAware;

class LazyContext extends ContainerAware implements ContextInterface
{
    /**
     * @var string
     */
    protected $contextName;

    /**
     * @var string
     */
    protected $paymentServiceId;

    /**
     * @var string
     */
    protected $storageServiceId;
    
    /**
     * @param string $contextName
     * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
     */
    public function __construct(
        $contextName, 
        $paymentServiceId,
        $storageServiceId 
    ) {
        $this->contextName = $contextName;
        $this->paymentServiceId = $paymentServiceId;
        $this->storageServiceId = $storageServiceId;
    }

    /**
     * {@inheritdoc}
     */
    public function getPayment()
    {
        return $this->container->get($this->paymentServiceId);
    }

    /**
     * {@inheritdoc}
     */
    public function getStorage()
    {
        if ($this->storageServiceId) {
            return $this->container->get($this->storageServiceId);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return $this->contextName;
    }
}