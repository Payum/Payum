<?php
namespace Payum\Bundle\PayumBundle\DependencyInjection\Factory\Payment;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

class OmnipayOffsitePaymentFactory extends OmnipayDirectPaymentFactory
{
    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'omnipay_offsite';
    }

    /**
     * {@inheritDoc}
     */
    protected function createPaymentDefinition(ContainerBuilder $container, $contextName, array $config)
    {
        $factoryId = 'payum.omnipay_bridge.factory';
        $container->setDefinition($factoryId, new Definition('Payum\OmnipayBridge\OffsitePaymentFactory', array(
            new Reference('payum.payment_factory'),
        )));

        $config['payum.factory'] = $this->getName();
        $config['payum.context'] = $contextName;

        $payment = new Definition('Payum\Core\Payment', array($config));
        $payment->setFactoryService($factoryId);
        $payment->setFactoryMethod('create');

        return $payment;
    }
}
