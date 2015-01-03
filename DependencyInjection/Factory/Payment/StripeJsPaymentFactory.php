<?php
namespace Payum\Bundle\PayumBundle\DependencyInjection\Factory\Payment;

use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;

class StripeJsPaymentFactory extends StripeCheckoutPaymentFactory implements PrependExtensionInterface
{
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
    protected function getPayumPaymentFactoryClass()
    {
        return 'Payum\Stripe\JsPaymentFactory';
    }
}