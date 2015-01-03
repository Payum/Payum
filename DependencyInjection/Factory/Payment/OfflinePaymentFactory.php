<?php
namespace Payum\Bundle\PayumBundle\DependencyInjection\Factory\Payment;

class OfflinePaymentFactory extends AbstractPaymentFactory
{
    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'offline';
    }

    /**
     * {@inheritDoc}
     */
    protected function getPayumPaymentFactoryClass()
    {
        return 'Payum\Offline\PaymentFactory';
    }

    /**
     * {@inheritDoc}
     */
    protected function getComposerPackage()
    {
        return 'payum/offline';
    }
}