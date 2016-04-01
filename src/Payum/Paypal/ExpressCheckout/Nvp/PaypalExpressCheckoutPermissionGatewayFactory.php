<?php
namespace Payum\Paypal\ExpressCheckout\Nvp;

use Payum\Core\Bridge\Spl\ArrayObject;

class PaypalExpressCheckoutPermissionGatewayFactory extends PaypalExpressCheckoutGatewayFactory
{

    /**
     * {@inheritDoc}
     */
    protected function populateConfig(ArrayObject $config)
    {
        parent::populateConfig($config);
        $config['payum.factory_title'] = 'PayPal ExpressCheckout via merchant token';
    }

    /**
     * {@inheritDoc}
     */
    protected function setDefaultApi(ArrayObject $config)
    {
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
