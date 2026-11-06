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

/**
 * @deprecated since 2.0, removed in 3.0. Register an OfflineConfig with PayumBuilder::registerGateway() instead.
 */
class OfflineGatewayFactory extends GatewayFactory
{
    protected function populateConfig(ArrayObject $config): void
    {
        trigger_deprecation(
            'payum/offline',
            '2.0.0',
            '%s is deprecated and will be removed in 3.0. Register %s with PayumBuilder::registerGateway() instead.',
            self::class,
            OfflineConfig::class,
        );

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
