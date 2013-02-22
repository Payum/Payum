<?php
namespace Payum\OmnipayBridge;

use Omnipay\Common\GatewayInterface;

use Payum\Payment as BasePayment;
use Payum\OmnipayBridge\Action\CaptureAction;
use Payum\OmnipayBridge\Action\StatusAction;

class Payment extends BasePayment
{
    /**
     * @var \Omnipay\Common\GatewayInterface
     */
    protected $gateway;

    /**
     * @param \Omnipay\Common\GatewayInterface $gateway
     */
    public function __construct(GatewayInterface $gateway)
    {
        $this->gateway = $gateway;
    }

    /**
     * @return \Omnipay\Common\GatewayInterface
     */
    public function getGateway()
    {
        return $this->gateway;
    }

    /**
     * @param \Omnipay\Common\GatewayInterface $gateway
     *
     * @return \Payum\OmnipayBridge\Payment
     */
    public static function create(GatewayInterface $gateway)
    {
        $payment = new static($gateway);

        $payment->addAction(new CaptureAction);
        $payment->addAction(new StatusAction);

        return $payment;
    }
}