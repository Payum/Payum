<?php

declare(strict_types=1);

namespace Payum\Core\Tests\Bc\Fixture;

use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\GatewayFactory;

/**
 * A 1.x gateway built to sit on every edge the BC promise is most likely to break on: sub-requests,
 * ExecuteSameRequestWithModelDetailsAction, a reply of its own, details written after a reply was thrown.
 *
 * Loaded by record.php against 1.x as well, so it uses nothing 2.x added.
 */
class AwkwardGatewayFactory extends GatewayFactory
{
    protected function populateConfig(ArrayObject $config): void
    {
        $config->defaults([
            'payum.factory_name' => 'awkward',
            'payum.factory_title' => 'Awkward',
            'payum.action.capture' => new AwkwardCaptureAction(),
            'payum.action.redirect' => new AwkwardRedirectAction(),
            'payum.action.authorize' => new AwkwardAuthorizeAction(),
            'payum.action.refund' => new AwkwardRefundAction(),
            'payum.action.sync' => new AwkwardSyncAction(),
            'payum.action.notify' => new AwkwardNotifyAction(),
            'payum.action.status' => new AwkwardStatusAction(),
            'payum.action.convert_payment' => new AwkwardConvertPaymentAction(),
        ]);

        $config['payum.paths'] = array_replace([
            'AwkwardBc' => __DIR__ . '/views',
        ], $config['payum.paths'] ?: []);
    }
}
