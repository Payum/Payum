<?php
namespace Payum\Be2Bill;

use Payum\Be2Bill\Action\CaptureOffsiteAction;
use Payum\Be2Bill\Action\CaptureOffsiteNullAction;
use Payum\Be2Bill\Action\NotifyAction;
use Payum\Be2Bill\Action\NotifyNullAction;
use Payum\Core\Bridge\Spl\ArrayObject;

class Be2BillOffsiteGatewayFactory extends Be2BillDirectGatewayFactory
{
    /**
     * {@inheritDoc}
     */
    protected function populateConfig(ArrayObject $config)
    {
        $config->defaults([
            'payum.factory_name' => 'be2bill_offsite',
            'payum.factory_title' => 'Be2Bill Offsite',
            'payum.action.capture' => new CaptureOffsiteAction(),
            'payum.action.capture_null' => new CaptureOffsiteNullAction(),
            'payum.action.notify_null' => new NotifyNullAction(),
            'payum.action.notify' => new NotifyAction(),
        ]);

        parent::populateConfig($config);
    }
}
