<?php
namespace Payum\Core\Action;

use Payum\Core\PaymentAwareInterface;
use Payum\Core\PaymentInterface;

abstract class PaymentAwareAction implements ActionInterface, PaymentAwareInterface
{
    /**
     * @var PaymentInterface
     */
    protected $payment;

    /**
     * {@inheritDoc}
     */
    public function setPayment(PaymentInterface $payment)
    {
        $this->payment = $payment;
    }
}
