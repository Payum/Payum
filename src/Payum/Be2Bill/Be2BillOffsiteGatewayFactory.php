<?php
namespace Payum\Be2Bill;

use Payum\Be2Bill\Action\CaptureOffsiteAction;
use Payum\Core\Bridge\Spl\ArrayObject;

class Be2BillOffsiteGatewayFactory extends Be2BillDirectGatewayFactory
{
    /**
     * {@inheritDoc}
     */
    public function createConfig(array $config = array())
    {
        $config = ArrayObject::ensureArrayObject($config);
        $config->defaults(array(
            'payum.factory_name' => 'be2bill_offsite',
            'payum.factory_title' => 'Be2Bill Offsite',
            'payum.action.capture' => new CaptureOffsiteAction(),
        ));

        return parent::createConfig((array) $config);
    }
}
