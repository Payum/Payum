<?php
namespace Payum\Core;

interface PaymentAwareInterface
{
    /**
     * @param \Payum\Core\PaymentInterface $payment
     */
    function setPayment(PaymentInterface $payment);
}