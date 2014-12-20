<?php
namespace Payum\Be2Bill;

use Payum\Be2Bill\Action\CaptureOffsiteAction;
use Payum\Core\Bridge\Spl\ArrayObject;

class OffsitePaymentFactory extends DirectPaymentFactory
{
    /**
     * {@inheritDoc}
     */
    public function createConfig(array $config = array())
    {
        $config = ArrayObject::ensureArrayObject($config);
        $config->defaults(array(
            'payum.action.capture' => new CaptureOffsiteAction(),
        ));

        return parent::createConfig((array) $config);
    }
}
