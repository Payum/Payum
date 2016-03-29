<?php
namespace Payum\Be2Bill;

use Payum\Be2Bill\Action\CaptureOffsiteAction;
use Payum\Be2Bill\Action\CaptureOffsiteNullAction;
use Payum\Be2Bill\Action\NotifyAction;
use Payum\Be2Bill\Action\NotifyNullAction;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Model\DetailsAggregateInterface;
use Payum\Core\Model\PaymentInterface;
use Payum\Core\Request\Capture;
use Payum\Core\Request\GetStatusInterface;
use Payum\Core\Request\Notify;

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
            'payum.supported_actions' => [
                Notify::class => [\ArrayAccess::class, DetailsAggregateInterface::class],
                GetStatusInterface::class => [\ArrayAccess::class, DetailsAggregateInterface::class],
                Capture::class => [\ArrayAccess::class, PaymentInterface::class],
            ],
            
            'payum.action.capture' => new CaptureOffsiteAction(),
            'payum.action.capture_null' => new CaptureOffsiteNullAction(),
            'payum.action.notify_null' => new NotifyNullAction(),
            'payum.action.notify' => new NotifyAction(),
        ]);

        parent::populateConfig($config);
    }
}
