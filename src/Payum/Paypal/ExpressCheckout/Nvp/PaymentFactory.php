<?php
namespace Payum\Paypal\ExpressCheckout\Nvp;

use Payum\Core\PaymentFactoryInterface;

abstract class PaymentFactory implements PaymentFactoryInterface
{
    /**
     * {@inheritDoc}
     */
    public static function create(array $options = array())
    {
        return static::createBuilder($options)->getPayment();
    }

    /**
     * {@inheritDoc}
     */
    public static function createBuilder(array $options = array())
    {
        $builder = new PaymentBuilder();

        foreach ($options as $name => $value) {
            $builder->set('payum.options', $name, $value);
        }

        return $builder;
    }

    /**
     */
    private  function __construct()
    {
    }
}
