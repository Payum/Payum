<?php
namespace Payum\Core;

interface PaymentAwareInterface
{
    /**
     * @param \Payum\Core\PaymentInterface $payment
     */
    public function setPayment(PaymentInterface $payment);
}
