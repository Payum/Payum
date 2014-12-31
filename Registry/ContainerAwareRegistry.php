<?php
namespace Payum\Bundle\PayumBundle\Registry;

use Payum\Core\Registry\AbstractRegistry;
use Payum\Core\Registry\PaymentRegistryInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class ContainerAwareRegistry extends AbstractRegistry implements ContainerAwareInterface 
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @var
     */
    protected $dynamicPaymentRegistry;

    /**
     * @param PaymentRegistryInterface $dynamicPaymentRegistry
     */
    public function __construct(PaymentRegistryInterface $dynamicPaymentRegistry)
    {
        $this->dynamicPaymentRegistry = $dynamicPaymentRegistry;
    }
    
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
    public function getPayment($name = null)
    {
        try {
            return parent::getPayment($name);
        } catch (\Exception $e) {
            return $this->dynamicPaymentRegistry->getPayment($name ?: $this->getDefaultPaymentName());
        }
    }

    /**
     * {@inheritDoc}
     */
    protected function getService($id)
    {
        return $this->container->get($id);
    }
}