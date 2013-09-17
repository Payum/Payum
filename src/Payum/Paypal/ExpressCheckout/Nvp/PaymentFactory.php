<?php
namespace Payum\Paypal\ExpressCheckout\Nvp;

use Payum\Payment;
use Payum\Extension\EndlessCycleDetectorExtension;
use Payum\Action\CaptureDetailsAggregatedModelAction;
use Payum\Action\StatusDetailsAggregatedModelAction;
use Payum\Action\SyncDetailsAggregatedModelAction;
use Payum\Paypal\ExpressCheckout\Nvp\Action\Api\CreateRecurringPaymentProfileAction;
use Payum\Paypal\ExpressCheckout\Nvp\Action\Api\DoExpressCheckoutPaymentAction;
use Payum\Paypal\ExpressCheckout\Nvp\Action\Api\GetExpressCheckoutDetailsAction;
use Payum\Paypal\ExpressCheckout\Nvp\Action\Api\GetRecurringPaymentsProfileDetailsAction;
use Payum\Paypal\ExpressCheckout\Nvp\Action\Api\GetTransactionDetailsAction;
use Payum\Paypal\ExpressCheckout\Nvp\Action\Api\SetExpressCheckoutAction;
use Payum\Paypal\ExpressCheckout\Nvp\Action\Api\AuthorizeTokenAction;
use Payum\Paypal\ExpressCheckout\Nvp\Action\Api\ManageRecurringPaymentsProfileStatusAction;
use Payum\Paypal\ExpressCheckout\Nvp\Action\CaptureAction;
use Payum\Paypal\ExpressCheckout\Nvp\Action\PaymentDetailsStatusAction;
use Payum\Paypal\ExpressCheckout\Nvp\Action\PaymentDetailsSyncAction;
use Payum\Paypal\ExpressCheckout\Nvp\Action\RecurringPaymentDetailsStatusAction;
use Payum\Paypal\ExpressCheckout\Nvp\Action\RecurringPaymentDetailsSyncAction;

abstract class PaymentFactory
{
    /**
     * @param Api $api
     *
     * @return \Payum\Payment
     */
    public static function create(Api $api)
    {
        $payment = new Payment;

        $payment->addApi($api);

        $payment->addExtension(new EndlessCycleDetectorExtension);

        $payment->addAction(new SetExpressCheckoutAction);
        $payment->addAction(new GetExpressCheckoutDetailsAction);
        $payment->addAction(new GetTransactionDetailsAction);
        $payment->addAction(new DoExpressCheckoutPaymentAction);
        $payment->addAction(new CreateRecurringPaymentProfileAction);
        $payment->addAction(new GetRecurringPaymentsProfileDetailsAction);

        $payment->addAction(new CaptureAction);
        $payment->addAction(new PaymentDetailsStatusAction);
        $payment->addAction(new PaymentDetailsSyncAction);
        $payment->addAction(new RecurringPaymentDetailsStatusAction);
        $payment->addAction(new RecurringPaymentDetailsSyncAction);
        $payment->addAction(new ManageRecurringPaymentsProfileStatusAction);
        $payment->addAction(new AuthorizeTokenAction);

        $payment->addAction(new CaptureDetailsAggregatedModelAction);
        $payment->addAction(new SyncDetailsAggregatedModelAction);
        $payment->addAction(new StatusDetailsAggregatedModelAction);

        return $payment;
    }

    /**
     */
    private  function __construct()
    {
    }
}
