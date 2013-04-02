<?php
namespace Payum\Action;

use Payum\PaymentAwareInterface;
use Payum\PaymentInterface;

abstract class PaymentAwareAction implements ActionInterface, PaymentAwareInterface
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