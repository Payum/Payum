<?php
namespace Payum\Offline;

use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\GatewayFactory as CoreGatewayFactory;
use Payum\Core\GatewayFactory;
use Payum\Core\GatewayFactoryInterface;
use Payum\Offline\Action\CaptureAction;
use Payum\Offline\Action\ConvertPaymentAction;
use Payum\Offline\Action\StatusAction;

class OfflineGatewayFactory extends GatewayFactory
{
    /**
     * {@inheritDoc}
     */
    protected function populateConfig(ArrayObject $config)
    {
        $config->defaults(array(
            'payum.factory_name' => 'offline',
            'payum.factory_title' => 'Offline',
            'payum.action.capture' => new CaptureAction(),
            'payum.action.status' => new StatusAction(),
            'payum.action.convert_payment' => new ConvertPaymentAction(),
        ));
    }
}
