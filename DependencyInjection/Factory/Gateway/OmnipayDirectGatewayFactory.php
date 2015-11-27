<?php
namespace Payum\Bundle\PayumBundle\DependencyInjection\Factory\Gateway;

/**
 * @deprecated since 1.0.0-BETA2
 */
class OmnipayDirectGatewayFactory extends OmnipayGatewayFactory
{
    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'omnipay_direct';
    }

    /**
     * {@inheritDoc}
     */
    protected function getPayumGatewayFactoryClass()
    {
        return \Payum\OmnipayBridge\OmnipayDirectGatewayFactory::class;
    }
}
