<?php
namespace Payum\Bundle\PayumBundle\DependencyInjection\Factory\Gateway;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\DependencyInjection\Parameter;

class StripeJsGatewayFactory extends StripeCheckoutGatewayFactory implements PrependExtensionInterface
{
    /**
     * {@inheritDoc}
     */
    public function load(ContainerBuilder $container)
    {
        parent::load($container);

        $container->setParameter('payum.stripe_js.template.obtain_checkout_token', '@PayumStripe/Action/obtain_js_token.html.twig');
    }

    /**
     * {@inheritDoc}
     */
    protected function createFactoryConfig()
    {
        $config = parent::createFactoryConfig();
        $config['payum.template.obtain_token'] = new Parameter('payum.stripe_js.template.obtain_checkout_token');

        return $config;
    }

    /**
     * {@inheritDoc}
     */
    public function getName()
    {
        return 'stripe_js';
    }

    /**
     * {@inheritDoc}
     */
    protected function getPayumGatewayFactoryClass()
    {
        return 'Payum\Stripe\StripeJsGatewayFactory';
    }
}