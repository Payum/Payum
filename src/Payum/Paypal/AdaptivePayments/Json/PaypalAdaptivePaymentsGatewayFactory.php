<?php
namespace Payum\Paypal\AdaptivePayments\Json;

use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\GatewayFactory;
use Payum\Paypal\AdaptivePayments\Json\Action\Api\AuthorizeKeyAction;
use Payum\Paypal\AdaptivePayments\Json\Action\ConvertPaymentAction;
use Payum\Paypal\AdaptivePayments\Json\Action\Api\PayAction;
use Payum\Paypal\AdaptivePayments\Json\Action\Api\PaymentDetailsAction;
use Payum\Paypal\AdaptivePayments\Json\Action\CaptureAction;
use Payum\Paypal\AdaptivePayments\Json\Action\StatusAction;
use Payum\Paypal\AdaptivePayments\Json\Action\SyncAction;

class PaypalAdaptivePaymentsGatewayFactory extends GatewayFactory
{
    /**
     * {@inheritDoc}
     */
    protected function populateConfig(ArrayObject $config)
    {
        $config->defaults(array(
            'payum.factory_name' => 'paypal_adaptive_payments_json',
            'payum.factory_title' => 'PayPal Adaptive Payments',
            
            'payum.action.api.pay' => new PayAction(),
            'payum.action.api.payment_details' => new PaymentDetailsAction(),
            'payum.action.api.convert_payment' => new ConvertPaymentAction(),
            'payum.action.api.authorize_key' => new AuthorizeKeyAction(),
            'payum.action.api.capture' => new CaptureAction(),
            'payum.action.api.sync' => new SyncAction(),
            'payum.action.api.status' => new StatusAction(),
        ));

        if (false == $config['payum.api']) {
            $config['payum.default_options'] = array(
                'user_id' => '',
                'password' => '',
                'signature' => '',
                'application_id' => '',
                'sandbox' => true,
            );
            $config->defaults($config['payum.default_options']);
            $config['payum.required_options'] = array('user_id', 'password', 'signature', 'application_id');

            $config['payum.api'] = function (ArrayObject $config) {
                $config->validateNotEmpty($config['payum.required_options']);

                $paypalConfig = [
                    'user_id' => $config['user_id'],
                    'password' => $config['password'],
                    'application_id' => $config['application_id'],
                    'sandbox' => $config['sandbox'],
                    'signature' => $config['signature'],
                    'subject' => $config['subject'],
                    'version' => $config['version'],
                ];

                return new Api($paypalConfig, $config['payum.http_client'], $config['httplug.message_factory']);
            };
        }
    }
}
