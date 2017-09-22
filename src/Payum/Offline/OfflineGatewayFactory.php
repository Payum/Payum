<?php
namespace Payum\Offline;

use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\GatewayFactory;
use Payum\Offline\Action\AuthorizeAction;
use Payum\Offline\Action\CaptureAction;
use Payum\Offline\Action\ConvertPaymentAction;
use Payum\Offline\Action\ConvertPayoutAction;
use Payum\Offline\Action\PayoutAction;
use Payum\Offline\Action\RefundAction;
use Payum\Offline\Action\StatusAction;

class OfflineGatewayFactory extends GatewayFactory
{
    /**
     * {@inheritDoc}
     */
    protected function populateConfig(ArrayObject $config)
    {
        $config->defaults([
            'payum.factory_name' => 'offline',
            'payum.factory_title' => 'Offline',
            'payum.action.capture' => new CaptureAction(),
            'payum.action.authorize' => new AuthorizeAction(),
            'payum.action.payout' => new PayoutAction(),
            'payum.action.refund' => new RefundAction(),
            'payum.action.status' => new StatusAction(),
            'payum.action.convert_payment' => new ConvertPaymentAction(),
            'payum.action.convert_payout' => new ConvertPayoutAction(),
        ]);
    }
}
