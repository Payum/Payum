<?php
namespace Payum\Bundle\PayumBundle\DependencyInjection\Factory\Gateway;

class Be2BillOffsiteGatewayFactory extends Be2BillDirectGatewayFactory
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
    protected function getPayumGatewayFactoryClass()
    {
        return 'Payum\Be2Bill\Be2BillOffsiteGatewayFactory';
    }
}