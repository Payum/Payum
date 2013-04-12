<?php
namespace Payum\Registry;

use Payum\PaymentInterface;
use Payum\Exception\InvalidArgumentException;

interface PaymentRegistryInterface 
{
    /**
     * @return string
     */
    function getDefaultPaymentName();

    /**
     * @param string|null $name
     * 
     * @throws InvalidArgumentException if payment with such name not exist
     * 
     * @return PaymentInterface
     */
    function getPayment($name = null);
}