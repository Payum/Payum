<?php
namespace Payum\Paypal\Rest;

use PayPal\Api\Payment;
use PayPal\Rest\ApiContext;
use PayPal\Auth\OAuthTokenCredential;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\GatewayFactory;
use Payum\Core\Request\Capture;
use Payum\Core\Request\GetStatusInterface;
use Payum\Core\Request\Sync;
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
        if (!class_exists('\PayPal\Api\Payment')) {
            throw new \LogicException('You must install "paypal/rest-api-sdk-php" library.');
        }

        $config->defaults(array(
            'payum.factory_name' => 'paypal_rest',
            'payum.factory_title' => 'PayPal Rest',
            'payum.supported_actions' => [
                Sync::class => [Payment::class],
                GetStatusInterface::class => [Payment::class],
                Capture::class => [Payment::class],
            ],

            'payum.action.capture' => new CaptureAction(),
            'payum.action.sync' => new SyncAction(),
            'payum.action.status' => new StatusAction(),
        ));

        if (false == $config['payum.api']) {
            $config['payum.default_options'] = array(
                'client_id' => '',
                'client_secret' => '',
                'config_path' => '',
            );
            $config->defaults($config['payum.default_options']);

            $config['payum.required_options'] = array('client_id', 'client_secret', 'config_path');
            $config['payum.api'] = function (ArrayObject $config) {
                $config->validateNotEmpty($config['payum.required_options']);

                if (!defined('PP_CONFIG_PATH')) {
                    define('PP_CONFIG_PATH', $config['config_path']);
                } elseif (PP_CONFIG_PATH !== $config['config_path']) {
                    throw new InvalidArgumentException(sprintf('Given "config_path" is invalid. Should be equal to the defined "PP_CONFIG_PATH": %s.', PP_CONFIG_PATH));
                }

                $credential = new OAuthTokenCredential($config['client_id'], $config['client_secret']);
                $config['payum.api'] = new ApiContext($credential);
            };
        }
    }
}
