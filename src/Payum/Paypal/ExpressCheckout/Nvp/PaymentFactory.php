<?php
namespace Payum\Paypal\ExpressCheckout\Nvp;

use Payum\Core\Action\CaptureOrderAction;
use Payum\Core\Action\ExecuteSameRequestWithModelDetailsAction;
use Payum\Core\Action\GetHttpRequestAction;
use Payum\Core\Bridge\Buzz\ClientFactory;
use Payum\Core\Payment;
use Payum\Core\Extension\EndlessCycleDetectorExtension;
use Payum\Core\PaymentFactoryInterface;
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
use Payum\Paypal\ExpressCheckout\Nvp\Action\FillOrderDetailsAction;
use Payum\Paypal\ExpressCheckout\Nvp\Action\NotifyAction;
use Payum\Paypal\ExpressCheckout\Nvp\Action\PaymentDetailsStatusAction;
use Payum\Paypal\ExpressCheckout\Nvp\Action\PaymentDetailsSyncAction;
use Payum\Paypal\ExpressCheckout\Nvp\Action\RecurringPaymentDetailsStatusAction;
use Payum\Paypal\ExpressCheckout\Nvp\Action\RecurringPaymentDetailsSyncAction;

class PaymentFactory implements PaymentFactoryInterface
{
    /**
     * {@inheritDoc}
     */
    public function create(array $options = array())
    {
        $payment = new Payment;

        $payment->addApi(new Api($options, ClientFactory::createCurl()));

        $payment->addExtension(new EndlessCycleDetectorExtension);

        $payment->addAction(new SetExpressCheckoutAction);
        $payment->addAction(new GetExpressCheckoutDetailsAction);
        $payment->addAction(new GetTransactionDetailsAction);
        $payment->addAction(new DoExpressCheckoutPaymentAction);
        $payment->addAction(new CreateRecurringPaymentProfileAction);
        $payment->addAction(new GetRecurringPaymentsProfileDetailsAction);

        $payment->addAction(new CaptureAction);
        $payment->addAction(new FillOrderDetailsAction);
        $payment->addAction(new NotifyAction);
        $payment->addAction(new PaymentDetailsStatusAction);
        $payment->addAction(new PaymentDetailsSyncAction);
        $payment->addAction(new RecurringPaymentDetailsStatusAction);
        $payment->addAction(new RecurringPaymentDetailsSyncAction);
        $payment->addAction(new ManageRecurringPaymentsProfileStatusAction);
        $payment->addAction(new CreateBillingAgreementAction);
        $payment->addAction(new DoReferenceTransactionAction);
        $payment->addAction(new AuthorizeTokenAction);
        $payment->addAction(new GetHttpRequestAction);

        $payment->addAction(new CaptureOrderAction);

        $payment->addAction(new ExecuteSameRequestWithModelDetailsAction);

        return $payment;
    }
}
