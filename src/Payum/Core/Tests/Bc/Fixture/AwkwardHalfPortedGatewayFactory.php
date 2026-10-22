<?php

declare(strict_types=1);

namespace Payum\Core\Tests\Bc\Fixture;

use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Legacy\HandlerToActionAdapter;

/**
 * The awkward 1.x factory with sync swapped for the handler it was ported to. 2.x only; record.php never
 * loads it.
 */
final class AwkwardHalfPortedGatewayFactory extends AwkwardGatewayFactory
{
    protected function populateConfig(ArrayObject $config): void
    {
        $config['payum.action.sync'] = new HandlerToActionAdapter(new AwkwardSyncHandler());

        parent::populateConfig($config);
    }
}
