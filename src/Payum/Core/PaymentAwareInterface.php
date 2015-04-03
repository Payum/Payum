<?php
namespace Payum\Core;

/**
 * @deprecated use GatewayAwareInterface
 */
interface PaymentAwareInterface
{
    /**
     * @param \Payum\Core\PaymentInterface $payment
     */
    public function setPayment(PaymentInterface $payment);
}
