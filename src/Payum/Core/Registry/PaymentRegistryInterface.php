<?php
namespace Payum\Core\Registry;

use Payum\Core\PaymentInterface;
use Payum\Core\Exception\InvalidArgumentException;

interface PaymentRegistryInterface 
{
    /**
     * @return string
     */
    function getDefaultPaymentName();

    /**
     * @param string|null $name
     * 
     * @throws \Payum\Core\Exception\InvalidArgumentException if payment with such name not exist
     * 
     * @return \Payum\Core\PaymentInterface
     */
    function getPayment($name = null);

    /**
     * @return \Payum\Core\PaymentInterface[]
     */
    function getPayments();
}