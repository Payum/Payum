<?php
namespace Payum\Paypal\ExpressCheckout\Nvp;

use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\InvalidArgumentException;
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
use Payum\Paypal\ExpressCheckout\Nvp\Action\AuthorizeAction;
use Payum\Paypal\ExpressCheckout\Nvp\Action\CaptureAction;
use Payum\Paypal\ExpressCheckout\Nvp\Action\ConvertPaymentAction;
use Payum\Paypal\ExpressCheckout\Nvp\Action\NotifyAction;
use Payum\Paypal\ExpressCheckout\Nvp\Action\PaymentDetailsStatusAction;
use Payum\Paypal\ExpressCheckout\Nvp\Action\PaymentDetailsSyncAction;
use Payum\Paypal\ExpressCheckout\Nvp\Action\RecurringPaymentDetailsStatusAction;
use Payum\Paypal\ExpressCheckout\Nvp\Action\RecurringPaymentDetailsSyncAction;
use Payum\Paypal\ExpressCheckout\Nvp\ApiPermission;

class PaypalExpressCheckoutPermissionGatewayFactory extends PaypalExpressCheckoutGatewayFactory
{

    /**
     * {@inheritDoc}
     */
    protected function getConfigDefaults()
    {
        $defaults = parent::getConfigDefaults();
        $defaults['payum.factory_title'] = 'PayPal ExpressCheckout via merchant token';
        return $defaults;
    }

    /**
     * {@inheritDoc}
     */
    protected function setDefaultApi(ArrayObject $config)
    {
        if (false == $config['payum.api']) {
            $config['payum.default_options'] = array(
                'username' => '',
                'password' => '',
                'signature' => '',
                'token' => '',
                'tokenSecret' => '',
                'third_party_subject' => '',
                'sandbox' => true,
            );
            $config->defaults($config['payum.default_options']);
            $config['payum.required_options'] = array('username', 'password', 'signature', 'token', 'tokenSecret', 'third_party_subject');

            $config['payum.api'] = function (ArrayObject $config) {
                $config->validateNotEmpty($config['payum.required_options']);

                $paypalConfig = array(
                    'username' => $config['username'],
                    'password' => $config['password'],
                    'signature' => $config['signature'],
                    'token' => $config['token'],
                    'tokenSecret' => $config['tokenSecret'],
                    'third_party_subject' => $config['third_party_subject'],
                    'sandbox' => $config['sandbox'],
                );

                return new ApiPermission($paypalConfig, $config['payum.http_client'], $config['httplug.message_factory']);
            };
        }
    }
}
