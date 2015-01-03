<?php
namespace Payum\Bundle\PayumBundle\DependencyInjection\Factory\Payment;

class Be2BillOffsitePaymentFactory extends Be2BillDirectPaymentFactory
{
    /**
     * {@inheritDoc}
     */
    public function getName()
    {
        return 'be2bill_offsite';
    }

    /**
     * {@inheritDoc}
     */
    protected function getPayumPaymentFactoryClass()
    {
        return 'Payum\Be2Bill\OffsitePaymentFactory';
    }
}