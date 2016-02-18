<?php
namespace Payum\Sofort;

use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\LogicException;
use Payum\Core\GatewayFactory;
use Payum\Sofort\Action\Api\CreateTransactionAction;
use Payum\Sofort\Action\Api\GetTransactionDataAction;
use Payum\Sofort\Action\Api\RefundTransactionAction;
use Payum\Sofort\Action\CaptureAction;
use Payum\Sofort\Action\ConvertPaymentAction;
use Payum\Sofort\Action\NotifyAction;
use Payum\Sofort\Action\RefundAction;
use Payum\Sofort\Action\StatusAction;
use Payum\Sofort\Action\SyncAction;
use Sofort\SofortLib\Sofortueberweisung;

class SofortGatewayFactory extends GatewayFactory
{
    /**
     * {@inheritdoc}
     */
    protected function populateConfig(ArrayObject $config)
    {
        if (false == class_exists(Sofortueberweisung::class)) {
            throw new LogicException('You must install "sofort/sofortlib-php:^3.0" library.');
        }

        $config->defaults(array(
            'payum.factory_name' => 'sofort',
            'payum.factory_title' => 'Sofort',
            'payum.action.capture' => new CaptureAction(),
            'payum.action.status' => new StatusAction(),
            'payum.action.notify' => new NotifyAction(),
            'payum.action.sync' => new SyncAction(),
            'payum.action.refund' => new RefundAction(),
            'payum.action.convert_payment' => new ConvertPaymentAction(),

            'payum.action.api.create_transaction' => new CreateTransactionAction(),
            'payum.action.api.get_transaction_data' => new GetTransactionDataAction(),
            'payum.action.api.refund_transaction' => new RefundTransactionAction(),
        ));

        if (false == $config['payum.api']) {
            $config['payum.default_options'] = [
                'config_key' => '',
                'abort_url' => '',
            ];
            $config->defaults($config['payum.default_options']);
            $config['payum.required_options'] = ['config_key'];

            $config['payum.api'] = function (ArrayObject $config) {
                $config->validateNotEmpty($config['payum.required_options']);

                return new Api([
                    'config_key' => $config['config_key'],
                    'abort_url' => $config['abort_url'],
                ]);
            };
        }
    }
}
