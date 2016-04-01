<?php
namespace Payum\Paypal\ExpressCheckout\Nvp;

use Payum\Core\Bridge\Spl\ArrayObject;
use PayPal\Auth\Oauth\AuthSignature;

class PaypalExpressCheckoutPermissionGatewayFactory extends PaypalExpressCheckoutGatewayFactory
{

    /**
     * {@inheritDoc}
     */
    protected function populateConfig(ArrayObject $config)
    {
        if (false == class_exists(AuthSignature::class)) {
            throw new LogicException('You must install "paypal/sdk-core-php:~3.0" library.');
        }
        parent::populateConfig($config);
        $config['payum.factory_title'] = 'PayPal ExpressCheckout';
    }

    /**
     * {@inheritDoc}
     */
    protected function populateDefaultApi(ArrayObject $config)
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
