<?php
namespace Payum\Paypal\ExpressCheckout\Nvp;

use Payum\Payment;
use Payum\Extension\EndlessCycleDetectorExtension;
use Payum\Action\CapturePaymentInstructionAggregateAction;
use Payum\Action\StatusPaymentInstructionAggregateAction;
use Payum\Action\SyncPaymentInstructionAggregateAction;
use Payum\Paypal\ExpressCheckout\Nvp\Action\Api\CreateRecurringPaymentProfileAction;
use Payum\Paypal\ExpressCheckout\Nvp\Action\Api\DoExpressCheckoutPaymentAction;
use Payum\Paypal\ExpressCheckout\Nvp\Action\Api\GetExpressCheckoutDetailsAction;
use Payum\Paypal\ExpressCheckout\Nvp\Action\Api\GetTransactionDetailsAction;
use Payum\Paypal\ExpressCheckout\Nvp\Action\Api\SetExpressCheckoutAction;
use Payum\Paypal\ExpressCheckout\Nvp\Action\CaptureAction;
use Payum\Paypal\ExpressCheckout\Nvp\Action\StatusAction;
use Payum\Paypal\ExpressCheckout\Nvp\Action\SyncAction;

abstract class PaymentFactory
{
    /**
     * @param Api $api
     *
     * @return \Payum\Payment
     */
    public static function create(Api $api)
    {
        $payment = new Payment($api);

        $payment->addApi($api);

        $payment->addExtension(new EndlessCycleDetectorExtension);

        $payment->addAction(new SetExpressCheckoutAction);
        $payment->addAction(new GetExpressCheckoutDetailsAction);
        $payment->addAction(new GetTransactionDetailsAction);
        $payment->addAction(new DoExpressCheckoutPaymentAction);
        $payment->addAction(new CreateRecurringPaymentProfileAction);

        $payment->addAction(new CaptureAction);
        $payment->addAction(new StatusAction);
        $payment->addAction(new SyncAction);

        $payment->addAction(new CapturePaymentInstructionAggregateAction);
        $payment->addAction(new SyncPaymentInstructionAggregateAction);
        $payment->addAction(new StatusPaymentInstructionAggregateAction);

        return $payment;
    }

    /**
     */
    private  function __construct()
    {
    }
}