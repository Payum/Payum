<?php
namespace Payum\Paypal\ExpressCheckout\Nvp;

use Payum\Core\Action\ExecuteSameRequestWithModelDetailsAction;
use Payum\Core\Payment;
use Payum\Core\Extension\EndlessCycleDetectorExtension;
use Payum\Paypal\ExpressCheckout\Nvp\Action\Api\CreateRecurringPaymentProfileAction;
use Payum\Paypal\ExpressCheckout\Nvp\Action\Api\DoExpressCheckoutPaymentAction;
use Payum\Paypal\ExpressCheckout\Nvp\Action\Api\GetExpressCheckoutDetailsAction;
use Payum\Paypal\ExpressCheckout\Nvp\Action\Api\GetRecurringPaymentsProfileDetailsAction;
use Payum\Paypal\ExpressCheckout\Nvp\Action\Api\GetTransactionDetailsAction;
use Payum\Paypal\ExpressCheckout\Nvp\Action\Api\SetExpressCheckoutAction;
use Payum\Paypal\ExpressCheckout\Nvp\Action\Api\AuthorizeTokenAction;
use Payum\Paypal\ExpressCheckout\Nvp\Action\Api\ManageRecurringPaymentsProfileStatusAction;
use Payum\Paypal\ExpressCheckout\Nvp\Action\Api\CreateBillingAgreementAction;
use Payum\Paypal\ExpressCheckout\Nvp\Action\Api\DoReferenceTransactionAction;
use Payum\Paypal\ExpressCheckout\Nvp\Action\CaptureAction;
use Payum\Paypal\ExpressCheckout\Nvp\Action\NotifyAction;
use Payum\Paypal\ExpressCheckout\Nvp\Action\PaymentDetailsStatusAction;
use Payum\Paypal\ExpressCheckout\Nvp\Action\PaymentDetailsSyncAction;
use Payum\Paypal\ExpressCheckout\Nvp\Action\RecurringPaymentDetailsStatusAction;
use Payum\Paypal\ExpressCheckout\Nvp\Action\RecurringPaymentDetailsSyncAction;

abstract class PaymentFactory
{
    /**
     * @param Api $api
     *
     * @return \Payum\Core\Payment
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
        $payment->addAction(new NotifyAction);
        $payment->addAction(new PaymentDetailsStatusAction);
        $payment->addAction(new PaymentDetailsSyncAction);
        $payment->addAction(new RecurringPaymentDetailsStatusAction);
        $payment->addAction(new RecurringPaymentDetailsSyncAction);
        $payment->addAction(new ManageRecurringPaymentsProfileStatusAction);
        $payment->addAction(new CreateBillingAgreementAction);
        $payment->addAction(new DoReferenceTransactionAction);
        $payment->addAction(new AuthorizeTokenAction);
        $payment->addAction(new ExecuteSameRequestWithModelDetailsAction);

        return $payment;
    }

    /**
     */
    private  function __construct()
    {
    }
}
