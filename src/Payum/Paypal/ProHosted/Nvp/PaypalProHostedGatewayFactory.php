<?php

namespace Payum\Paypal\ProHosted\Nvp;

use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\GatewayFactory;
use Payum\Paypal\ProHosted\Nvp\Action\Api\GetTransactionDetailsAction;
use Payum\Paypal\ProHosted\Nvp\Action\Api\CreateButtonPaymentAction;
use Payum\Paypal\ProHosted\Nvp\Action\ConvertPaymentAction;
use Payum\Paypal\ProHosted\Nvp\Action\CaptureAction;
use Payum\Paypal\ProHosted\Nvp\Action\NotifyAction;
use Payum\Paypal\ProHosted\Nvp\Action\StatusAction;
use Payum\Paypal\ProHosted\Nvp\Action\SyncAction;

class PaypalProHostedGatewayFactory extends GatewayFactory
{
    /**
     * {@inheritDoc}
     */
    protected function populateConfig(ArrayObject $config)
    {
        $config->defaults([
            'payum.factory_name'                       => 'paypal_pro_hosted',
            'payum.factory_title'                      => 'Paypal Pro Hosted',
            'payum.action.capture'                     => new CaptureAction(),
            'payum.action.notify'                      => new NotifyAction(),
            'payum.action.status'                      => new StatusAction(),
            'payum.action.sync'                        => new SyncAction(),
            'payum.action.convert_payment'             => new ConvertPaymentAction(),
            'payum.action.api.get_transaction_details' => new GetTransactionDetailsAction(),
            'payum.action.api.create_button_payment'   => new CreateButtonPaymentAction(),
        ]);

        if (false == $config['payum.api']) {
            $config['payum.default_options'] = [
                'username'  => '',
                'password'  => '',
                'signature' => '',
                'business'  => '',
                'sandbox'   => true,
            ];

            $config->defaults($config['payum.default_options']);
            $config['payum.required_options'] = [
                'username',
                'password',
                'signature',
            ];

            $config['payum.api'] = function (ArrayObject $config) {
                $config->validateNotEmpty($config['payum.required_options']);

                $paypalConfig = array(
                    'username'  => $config['username'],
                    'password'  => $config['password'],
                    'signature' => $config['signature'],
                    'business'  => $config['business'],
                    'sandbox'   => $config['sandbox'],
                );

                return new Api($paypalConfig, $config['payum.http_client'], $config['httplug.message_factory']);
            };
        }
    }
}
