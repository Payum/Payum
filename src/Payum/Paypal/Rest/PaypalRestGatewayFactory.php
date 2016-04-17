<?php
namespace Payum\Paypal\Rest;

use PayPal\Auth\OAuthTokenCredential;
use PayPal\Rest\ApiContext;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\GatewayFactory;
use Payum\Paypal\Rest\Action\CaptureAction;
use Payum\Paypal\Rest\Action\StatusAction;
use Payum\Paypal\Rest\Action\SyncAction;
use Payum\Core\Exception\InvalidArgumentException;

class PaypalRestGatewayFactory extends GatewayFactory
{
    /**
     * {@inheritDoc}
     */
    protected function populateConfig(ArrayObject $config)
    {
        if (false == class_exists(ApiContext::class)) {
            throw new \LogicException('You must install "paypal/rest-api-sdk-php" library.');
        }

        $config->defaults([
            'payum.factory_name' => 'paypal_rest',
            'payum.factory_title' => 'PayPal Rest',

            'payum.action.capture' => new CaptureAction(),
            'payum.action.sync' => new SyncAction(),
            'payum.action.status' => new StatusAction(),
        ]);

        if (false == $config['payum.api']) {
            $config['payum.default_options'] = [
                'client_id' => '',
                'client_secret' => '',
                'config_path' => '',
            ];
            $config->defaults($config['payum.default_options']);

            $config['payum.required_options'] = ['client_id', 'client_secret', 'config_path'];
            $config['payum.api'] = function (ArrayObject $config) {
                $config->validateNotEmpty($config['payum.required_options']);

                if (false == defined('PP_CONFIG_PATH')) {
                    define('PP_CONFIG_PATH', $config['config_path']);
                } elseif (PP_CONFIG_PATH !== $config['config_path']) {
                    throw new InvalidArgumentException(sprintf('Given "config_path" is invalid. Should be equal to the defined "PP_CONFIG_PATH": %s.', PP_CONFIG_PATH));
                }

                $credential = new OAuthTokenCredential($config['client_id'], $config['client_secret']);
                return new ApiContext($credential);
            };
        }
    }
}
