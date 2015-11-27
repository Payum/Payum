<?php
namespace Payum\Bundle\PayumBundle\DependencyInjection\Factory\Gateway;

/**
 * @deprecated since 1.0.0-BETA2
 */
class OmnipayOffsiteGatewayFactory extends OmnipayGatewayFactory
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
        return \Payum\OmnipayBridge\OmnipayOffsiteGatewayFactory::class;
    }
}