<?php
namespace Payum\Core\Registry;

/**
 * @deprecated
 */
interface PaymentFactoryRegistryInterface
{
    /**
     * @param string $name
     *
     * @throws \Payum\Core\Exception\InvalidArgumentException if payment factory with such name not exist
     *
     * @return \Payum\Core\PaymentFactoryInterface
     */
    public function getPaymentFactory($name);

    /**
     * The key must be a factory name
     *
     * @return \Payum\Core\PaymentFactoryInterface[]
     */
    public function getPaymentFactories();
}
