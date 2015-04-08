<?php
namespace Payum\Bundle\PayumBundle\DependencyInjection\Factory\Gateway;

class OmnipayOffsiteGatewayFactory extends OmnipayDirectGatewayFactory
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
    protected function getPayumGatewayFactoryClass()
    {
        return 'Payum\OmnipayBridge\OmnipayOffsiteGatewayFactory';
    }
}
