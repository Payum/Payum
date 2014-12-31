<?php
namespace Payum\Core\Registry;

interface PaymentRegistryInterface
{
    /**
     * @return string
     */
    public function getDefaultPaymentName();

    /**
     * @param string|null $name
     *
     * @throws \Payum\Core\Exception\InvalidArgumentException if payment with such name not exist
     *
     * @return \Payum\Core\PaymentInterface
     */
    public function getPayment($name = null);

    /**
     * @return \Payum\Core\PaymentInterface[]
     */
    public function getPayments();
}
