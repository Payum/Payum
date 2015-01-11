<?php
namespace Payum\Core\Registry;

interface PaymentRegistryInterface
{
    /**
     * @param string $name
     *
     * @throws \Payum\Core\Exception\InvalidArgumentException if payment with such name not exist
     *
     * @return \Payum\Core\PaymentInterface
     */
    public function getPayment($name);

    /**
     * The key must be a payment name
     *
     * @return \Payum\Core\PaymentInterface[]
     */
    public function getPayments();
}
