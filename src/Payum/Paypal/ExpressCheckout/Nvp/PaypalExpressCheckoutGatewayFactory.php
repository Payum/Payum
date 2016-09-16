<?php
namespace Payum\Paypal\ExpressCheckout\Nvp;

use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\GatewayFactory;
use Payum\Paypal\ExpressCheckout\Nvp\Action\Api\ConfirmOrderAction;
use Payum\Paypal\ExpressCheckout\Nvp\Action\Api\CreateRecurringPaymentProfileAction;
use Payum\Paypal\ExpressCheckout\Nvp\Action\Api\DoCaptureAction;
use Payum\Paypal\ExpressCheckout\Nvp\Action\Api\DoExpressCheckoutPaymentAction;
use Payum\Paypal\ExpressCheckout\Nvp\Action\Api\GetExpressCheckoutDetailsAction;
use Payum\Paypal\ExpressCheckout\Nvp\Action\Api\GetRecurringPaymentsProfileDetailsAction;
use Payum\Paypal\ExpressCheckout\Nvp\Action\Api\GetTransactionDetailsAction;
use Payum\Paypal\ExpressCheckout\Nvp\Action\Api\SetExpressCheckoutAction;
use Payum\Paypal\ExpressCheckout\Nvp\Action\Api\AuthorizeTokenAction;
use Payum\Paypal\ExpressCheckout\Nvp\Action\Api\CancelRecurringPaymentsProfileAction;
use Payum\Paypal\ExpressCheckout\Nvp\Action\Api\ManageRecurringPaymentsProfileStatusAction;
use Payum\Paypal\ExpressCheckout\Nvp\Action\Api\CreateBillingAgreementAction;
use Payum\Paypal\ExpressCheckout\Nvp\Action\Api\DoReferenceTransactionAction;
use Payum\Paypal\ExpressCheckout\Nvp\Action\Api\UpdateRecurringPaymentProfileAction;
use Payum\Paypal\ExpressCheckout\Nvp\Action\Api\DoVoidAction;
use Payum\Paypal\ExpressCheckout\Nvp\Action\AuthorizeAction;
use Payum\Paypal\ExpressCheckout\Nvp\Action\CaptureAction;
use Payum\Paypal\ExpressCheckout\Nvp\Action\CancelAction;
use Payum\Paypal\ExpressCheckout\Nvp\Action\ConvertPaymentAction;
use Payum\Paypal\ExpressCheckout\Nvp\Action\NotifyAction;
use Payum\Paypal\ExpressCheckout\Nvp\Action\PaymentDetailsStatusAction;
use Payum\Paypal\ExpressCheckout\Nvp\Action\PaymentDetailsSyncAction;
use Payum\Paypal\ExpressCheckout\Nvp\Action\RecurringPaymentDetailsStatusAction;
use Payum\Paypal\ExpressCheckout\Nvp\Action\RecurringPaymentDetailsSyncAction;

class PaypalExpressCheckoutGatewayFactory extends GatewayFactory
{
    /**
     * {@inheritDoc}
     */
    protected function populateConfig(ArrayObject $config)
    {
        $config->defaults(array(
            'payum.factory_name' => 'paypal_express_checkout_nvp',
            'payum.factory_title' => 'PayPal ExpressCheckout',

            'payum.template.confirm_order' => '@PayumPaypalExpressCheckout/confirmOrder.html.twig',

            'payum.action.capture' => new CaptureAction(),
            'payum.action.cancel' => new CancelAction(),
            'payum.action.authorize' => new AuthorizeAction(),
            'payum.action.convert_payment' => new ConvertPaymentAction(),
            'payum.action.notify' => new NotifyAction(),
            'payum.action.status' => new PaymentDetailsStatusAction(),
            'payum.action.sync' => new PaymentDetailsSyncAction(),
            'payum.action.recurring_status' => new RecurringPaymentDetailsStatusAction(),
            'payum.action.recurring_sync' => new RecurringPaymentDetailsSyncAction(),

            'payum.action.api.set_express_checkout' => new SetExpressCheckoutAction(),
            'payum.action.api.get_express_checkout_details' => new GetExpressCheckoutDetailsAction(),
            'payum.action.api.get_transaction_details' => new GetTransactionDetailsAction(),
            'payum.action.api.do_express_checkout_payment' => new DoExpressCheckoutPaymentAction(),
            'payum.action.api.create_recurring_payment_profile' => new CreateRecurringPaymentProfileAction(),
            'payum.action.api.update_recurring_payment_profile' => new UpdateRecurringPaymentProfileAction(),
            'payum.action.api.get_recurring_payments_profile_details' => new GetRecurringPaymentsProfileDetailsAction(),
            'payum.action.api.cancel_recurring_payments_profile' => new CancelRecurringPaymentsProfileAction(),
            'payum.action.api.manage_recurring_payments_profile_status' => new ManageRecurringPaymentsProfileStatusAction(),
            'payum.action.api.create_billing_agreement' => new CreateBillingAgreementAction(),
            'payum.action.api.do_reference_transaction' => new DoReferenceTransactionAction(),
            'payum.action.api.do_capture' => new DoCaptureAction(),
            'payum.action.api.authorize_token' => new AuthorizeTokenAction(),
            'payum.action.api.do_void' => new DoVoidAction(),
            'payum.action.api.confirm_order' => function (ArrayObject $config) {
                return new ConfirmOrderAction($config['payum.template.confirm_order']);
            },
        ));

        if (false == $config['payum.api']) {
            $config['payum.default_options'] = array(
                'username' => '',
                'password' => '',
                'signature' => '',
                'sandbox' => true,
            );
            $config->defaults($config['payum.default_options']);
            $config['payum.required_options'] = array('username', 'password', 'signature');

            $config['payum.api'] = function (ArrayObject $config) {
                $config->validateNotEmpty($config['payum.required_options']);

                $paypalConfig = array(
                    'username' => $config['username'],
                    'password' => $config['password'],
                    'signature' => $config['signature'],
                    'sandbox' => $config['sandbox'],
                );

                return new Api($paypalConfig, $config['payum.http_client'], $config['httplug.message_factory']);
            };
        }

        $config['payum.paths'] = array_replace([
            'PayumPaypalExpressCheckout' => __DIR__.'/Resources/views',
        ], $config['payum.paths'] ?: []);
    }
}
