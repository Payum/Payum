<?php
namespace Payum\Paypal\ExpressCheckout\Nvp;

use Payum\Core\Payment;
use Payum\Core\PaymentBuilder as BasePaymentBuilder;
use Payum\Paypal\ExpressCheckout\Nvp\Action\Api\AuthorizeTokenAction;
use Payum\Paypal\ExpressCheckout\Nvp\Action\Api\CreateBillingAgreementAction;
use Payum\Paypal\ExpressCheckout\Nvp\Action\Api\CreateRecurringPaymentProfileAction;
use Payum\Paypal\ExpressCheckout\Nvp\Action\Api\DoExpressCheckoutPaymentAction;
use Payum\Paypal\ExpressCheckout\Nvp\Action\Api\DoReferenceTransactionAction;
use Payum\Paypal\ExpressCheckout\Nvp\Action\Api\GetExpressCheckoutDetailsAction;
use Payum\Paypal\ExpressCheckout\Nvp\Action\Api\GetRecurringPaymentsProfileDetailsAction;
use Payum\Paypal\ExpressCheckout\Nvp\Action\Api\GetTransactionDetailsAction;
use Payum\Paypal\ExpressCheckout\Nvp\Action\Api\ManageRecurringPaymentsProfileStatusAction;
use Payum\Paypal\ExpressCheckout\Nvp\Action\Api\SetExpressCheckoutAction;
use Payum\Paypal\ExpressCheckout\Nvp\Action\CaptureAction;
use Payum\Paypal\ExpressCheckout\Nvp\Action\FillOrderDetailsAction;
use Payum\Paypal\ExpressCheckout\Nvp\Action\NotifyAction;
use Payum\Paypal\ExpressCheckout\Nvp\Action\PaymentDetailsStatusAction;
use Payum\Paypal\ExpressCheckout\Nvp\Action\PaymentDetailsSyncAction;
use Payum\Paypal\ExpressCheckout\Nvp\Action\RecurringPaymentDetailsStatusAction;
use Payum\Paypal\ExpressCheckout\Nvp\Action\RecurringPaymentDetailsSyncAction;

class PaymentBuilder extends BasePaymentBuilder
{
    public function __construct()
    {
        parent::__construct();

        $this->set('payum.required_options', 'username', 'username');
        $this->set('payum.required_options', 'password', 'password');
        $this->set('payum.required_options', 'signature', 'signature');

        $this->set('payum.options', 'sandbox', true);
        
        $this->setAction('api.set_express_checkout', new SetExpressCheckoutAction);
        $this->setAction('api.get_express_checkout_details', new GetExpressCheckoutDetailsAction);
        $this->setAction('api.get_transaction_details', new GetTransactionDetailsAction);
        $this->setAction('api.do_express_checkout_payment', new DoExpressCheckoutPaymentAction);
        $this->setAction('api.create_recurring_payment_profile', new CreateRecurringPaymentProfileAction);
        $this->setAction('api.get_recurring_payments_profile', new GetRecurringPaymentsProfileDetailsAction);
        $this->setAction('api.manage_recurring_payments_profile_status', new ManageRecurringPaymentsProfileStatusAction);
        $this->setAction('api.create_billing_agreement', new CreateBillingAgreementAction);
        $this->setAction('api.do_reference_transaction', new DoReferenceTransactionAction);
        $this->setAction('api.authorize_token', new AuthorizeTokenAction);

        $this->setAction('capture', new CaptureAction);
        $this->setAction('fill_order_details', new FillOrderDetailsAction);
        $this->setAction('notify', new NotifyAction);
        $this->setAction('payment_details_status', new PaymentDetailsStatusAction);
        $this->setAction('payment_details_sync', new PaymentDetailsSyncAction);
        $this->setAction('recurring_payment_details_status', new RecurringPaymentDetailsStatusAction);
        $this->setAction('recurring_payment_details_sync', new RecurringPaymentDetailsSyncAction);
    }

    protected function buildPayment(Payment $payment)
    {
        parent::buildPayment($payment);

        $payment->addApi(new Api(
            array(
                'username' => $this->get('payum.options', 'username'),
                'signature' => $this->get('payum.options', 'signature'),
                'password' => $this->get('payum.options', 'password'),
                'sandbox' => (bool) $this->get('payum.options', 'sandbox'),
            ),
            $this->get('buzz', 'client')
        ));
    }
}