<?php
namespace Payum\AuthorizeNet\Aim;

use Payum\AuthorizeNet\Aim\Action\ConvertPaymentAction;
use Payum\AuthorizeNet\Aim\Action\CaptureAction;
use Payum\AuthorizeNet\Aim\Action\StatusAction;
use Payum\AuthorizeNet\Aim\Bridge\AuthorizeNet\AuthorizeNetAIM;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\GatewayFactory;
use Payum\Core\Model\GatewayMetaData;

class AuthorizeNetAimGatewayFactory extends GatewayFactory
{
    /**
     * {@inheritDoc}
     */
    protected function populateConfig(ArrayObject $config)
    {

        if (!class_exists('AuthorizeNetAIM')) {
            throw new \LogicException('You must install "authorizenet/authorizenet" library.');
        }

        $config->defaults(array(
            'payum.factory_name' => 'authorize_net_aim',
            'payum.factory_title' => 'Authorize.NET AIM',
            'payum.action.capture' => new CaptureAction(),
            'payum.action.status' => new StatusAction(),
            'payum.action.convert_payment' => new ConvertPaymentAction(),
        ));

        if (false == $config['payum.api']) {
            $metaData = new GatewayMetaData();
            $config['payum.default_options'] = $metaData->getAuthorizeMetaData();
            $config->defaults($config['payum.default_options']);
            $config['payum.required_options'] = array('login_id', 'transaction_key');

            $config['payum.api'] = function (ArrayObject $config) {
                $config->validateNotEmpty($config['payum.required_options']);
                $api = new AuthorizeNetAIM($config['login_id'], $config['transaction_key']);
                $api->setSandbox($config['sandbox']);

                return $api;
            };
        }
    }
}
