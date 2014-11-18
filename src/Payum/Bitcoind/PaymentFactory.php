<?php
namespace Payum\Bitcoind;

use Nbobtc\Bitcoind\BitcoindInterface;
use Payum\Bitcoind\Action\Api\SendToAddressAction;
use Payum\Bitcoind\Action\CaptureAction;
use Payum\Bitcoind\Action\StatusAction;
use Payum\Core\Extension\EndlessCycleDetectorExtension;
use Payum\Core\Payment;
use Payum\Core\PaymentInterface;

abstract class PaymentFactory
{
    /**
     * @param BitcoindInterface $bitcoind
     *
     * @return PaymentInterface
     */
    public static function create(BitcoindInterface $bitcoind)
    {
        $payment = new Payment();

        $payment->addExtension(new EndlessCycleDetectorExtension());

        $payment->addApi($bitcoind);

        $payment->addAction(new CaptureAction());
        $payment->addAction(new StatusAction());
        $payment->addAction(new SendToAddressAction());

        return $payment;
    }

    /**
     */
    private function __construct()
    {
    }
}
