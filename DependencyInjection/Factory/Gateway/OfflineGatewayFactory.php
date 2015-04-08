<?php
namespace Payum\Bundle\PayumBundle\DependencyInjection\Factory\Gateway;

class OfflineGatewayFactory extends AbstractGatewayFactory
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
    protected function getPayumGatewayFactoryClass()
    {
        return 'Payum\Offline\OfflineGatewayFactory';
    }

    /**
     * {@inheritDoc}
     */
    protected function getComposerPackage()
    {
        return 'payum/offline';
    }
}