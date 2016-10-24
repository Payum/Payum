<?php

namespace Payum\Tranzila;

use Payum\Tranzila\Action\ConvertPaymentAction;
use Payum\Tranzila\Action\CaptureAction;
use Payum\Tranzila\Action\StatusAction;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\GatewayFactory;
use Payum\Core\Model\GatewayMetaData;

class TranzilaGatewayFactory extends GatewayFactory
{
    /**
     * {@inheritDoc}
     */
    protected function populateConfig(ArrayObject $config)


    {
        $config->defaults(array(
            'payum.factory_name' => 'Tranzila',
            'payum.factory_title' => 'Tranzila',

            'payum.action.capture' => new CaptureAction(),
            'payum.action.status' => new StatusAction(),
            'payum.action.convert_payment' => new ConvertPaymentAction(),
        ));

        if (false == $config['payum.api']) {
            $metaData = new GatewayMetaData();
            $config['payum.default_options'] = $metaData->getTranzilaMetaData();
            $config->defaults($config['payum.default_options']);
            $config['payum.required_options'] = array('seller_payme_id', 'test_mode');

            $config['payum.api'] = function (ArrayObject $config) {
                $config->validateNotEmpty($config['payum.required_options']);

                return new Api(
                    array(
                        'seller_payme_id'=>$config['seller_payme_id'],
                        'test_mode'=>$config['test_mode'],
                    ),
                    $config['payum.http_client']
                );
            };
        }
    }
}