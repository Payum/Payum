<?php

namespace Payum\Payex\Action;

use Payum\Core\Action\ActionInterface;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\GatewayAwareInterface;
use Payum\Core\GatewayAwareTrait;
use Payum\Core\Request\Capture;
use Payum\Payex\Request\Api\AutoPayAgreement;

class AutoPayPaymentDetailsCaptureAction implements ActionInterface, GatewayAwareInterface
{
    use GatewayAwareTrait;

    /**
     * {@inheritDoc}
     */
    public function execute($request)
    {
        /** @var $request \Payum\Core\Request\Capture */
        RequestNotSupportedException::assertSupports($this, $request);

        $this->gateway->execute(new AutoPayAgreement($request->getModel()));
    }

    /**
     * {@inheritDoc}
     */
    public function supports($request)
    {
        if (false == (
            $request instanceof Capture &&
                $request->getModel() instanceof \ArrayAccess
        )) {
            return false;
        }

        $model = ArrayObject::ensureArrayObject($request->getModel());

        //Make sure it is not recurring payment. There is an other capture action for recurring payments;
        if (true == $model['recurring']) {
            return false;
        }

        if ($model['autoPay']) {
            return true;
        }

        return false;
    }
}
