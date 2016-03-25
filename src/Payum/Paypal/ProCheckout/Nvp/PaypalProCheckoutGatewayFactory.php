<?php
namespace Payum\Paypal\ProCheckout\Nvp;

use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\GatewayFactory;
use Payum\Paypal\ProCheckout\Nvp\Action\CaptureAction;
use Payum\Paypal\ProCheckout\Nvp\Action\RefundAction;
use Payum\Paypal\ProCheckout\Nvp\Action\ConvertPaymentAction;
use Payum\Paypal\ProCheckout\Nvp\Action\StatusAction;

class PaypalProCheckoutGatewayFactory extends GatewayFactory
{
    /**
     * {@inheritDoc}
     */
    protected function populateConfig(ArrayObject $config)
    {
        $config->defaults(array(
            'payum.factory_name' => 'paypal_pro_checkout_nvp',
            'payum.factory_title' => 'PayPal ProCheckout',

            'payum.action.capture' => new CaptureAction(),
            'payum.action.refund' => new RefundAction(),
            'payum.action.convert_payment' => new ConvertPaymentAction(),
            'payum.action.status' => new StatusAction(),
        ));

        if (false == $config['payum.api']) {
            $config['payum.default_options'] = array(
                'username' => '',
                'password' => '',
                'partner' => '',
                'vendor' => '',
                'tender' => '',
                'sandbox' => true,
            );
            $config->defaults($config['payum.default_options']);
            $config['payum.required_options'] = array('username', 'password', 'partner', 'vendor', 'tender');

            $config['payum.api'] = function (ArrayObject $config) {
                $config->validateNotEmpty($config['payum.required_options']);

                $paypalConfig = array(
                    'username' => $config['username'],
                    'password' => $config['password'],
                    'partner' => $config['partner'],
                    'vendor' => $config['vendor'],
                    'tender' => $config['tender'],
                    'sandbox' => $config['sandbox'],
                );

                return new Api($paypalConfig, $config['payum.http_client'], $config['httplug.message_factory']);
            };
        }
    }
}
