<?php

namespace Payum\PayTheFly;

use LogicException;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\GatewayFactory;
use Payum\PayTheFly\Action\CaptureAction;
use Payum\PayTheFly\Action\ConvertPaymentAction;
use Payum\PayTheFly\Action\NotifyAction;
use Payum\PayTheFly\Action\StatusAction;
use Payum\PayTheFly\Action\SyncAction;

class PayTheFlyGatewayFactory extends GatewayFactory
{
    protected function populateConfig(ArrayObject $config): void
    {
        $config->defaults([
            'payum.factory_name' => 'paythefly',
            'payum.factory_title' => 'PayTheFly Web3',

            'payum.action.capture' => new CaptureAction(),
            'payum.action.convert_payment' => new ConvertPaymentAction(),
            'payum.action.notify' => new NotifyAction(),
            'payum.action.status' => new StatusAction(),
            'payum.action.sync' => new SyncAction(),
        ]);

        if (! $config['payum.api']) {
            $config['payum.default_options'] = [
                'project_id' => '',
                'project_key' => '',
                'private_key' => '',
                'chain_id' => Constants::CHAIN_BSC,
                'verifying_contract' => '',
            ];
            $config->defaults($config['payum.default_options']);
            $config['payum.required_options'] = [
                'project_id',
                'project_key',
                'private_key',
                'chain_id',
                'verifying_contract',
            ];

            $config['payum.api'] = function (ArrayObject $config) {
                $config->validateNotEmpty($config['payum.required_options']);

                return new Api(
                    $config['project_id'],
                    $config['project_key'],
                    $config['private_key'],
                    (int) $config['chain_id'],
                    $config['verifying_contract']
                );
            };
        }
    }
}
