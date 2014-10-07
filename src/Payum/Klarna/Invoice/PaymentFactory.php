<?php
namespace Payum\Klarna\Invoice;

use Payum\Core\Action\GetHttpRequestAction;
use Payum\Core\Payment;
use Payum\Klarna\Invoice\Action\Api\ActivateAction;
use Payum\Klarna\Invoice\Action\Api\ActivateReservationAction;
use Payum\Klarna\Invoice\Action\Api\CancelReservationAction;
use Payum\Klarna\Invoice\Action\Api\CheckOrderStatusAction;
use Payum\Klarna\Invoice\Action\Api\CreditPartAction;
use Payum\Klarna\Invoice\Action\Api\GetAddressesAction;
use Payum\Klarna\Invoice\Action\Api\PopulateKlarnaFromDetailsAction;
use Payum\Klarna\Invoice\Action\Api\ReserveAmountAction;
use Payum\Klarna\Invoice\Action\AuthorizeAction;
use Payum\Klarna\Invoice\Action\CaptureAction;
use Payum\Klarna\Invoice\Action\RefundAction;
use Payum\Klarna\Invoice\Action\StatusAction;
use Payum\Klarna\Invoice\Action\SyncAction;

abstract class PaymentFactory
{
    public static function create(Config $config)
    {
        $payment = new Payment;

        $payment->addApi($config);

        $payment->addAction(new CaptureAction);
        $payment->addAction(new AuthorizeAction);
        $payment->addAction(new StatusAction);
        $payment->addAction(new SyncAction);
        $payment->addAction(new RefundAction);
        $payment->addAction(new GetHttpRequestAction);

        $payment->addAction(new ActivateAction);
        $payment->addAction(new ActivateReservationAction);
        $payment->addAction(new CancelReservationAction);
        $payment->addAction(new CheckOrderStatusAction);
        $payment->addAction(new GetAddressesAction);
        $payment->addAction(new PopulateKlarnaFromDetailsAction);
        $payment->addAction(new CreditPartAction);
        $payment->addAction(new ReserveAmountAction);

        return $payment;
    }

    /**
     */
    private  function __construct()
    {
    }
}
