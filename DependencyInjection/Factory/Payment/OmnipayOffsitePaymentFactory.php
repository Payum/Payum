<?php
namespace Payum\Bundle\PayumBundle\DependencyInjection\Factory\Payment;

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
    protected function getPayumPaymentFactoryClass()
    {
        return 'Payum\OmnipayBridge\OffsitePaymentFactory';
    }
}
