<?php
namespace Payum\Action;

use Payum\PaymentInterface;

abstract class ActionPaymentAware implements ActionPaymentAwareInterface
{
    /**
     * @var PaymentInterface
     */
    protected $payment;

    /**
     * {@inheritdoc}
     */
    public function setPayment(PaymentInterface $payment)
    {
        $this->payment = $payment;
    }
}