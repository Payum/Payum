<?php
namespace Payum\Paypal\Masspay\Nvp;

use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\GatewayFactory;
use Payum\Paypal\AdaptivePayments\Json\Action\Api\PayAction;
use Payum\Paypal\AdaptivePayments\Json\Action\Api\PaymentDetailsAction;

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
