<?php

namespace Payum\Payex\Action;

use Payum\Core\Action\ActionInterface;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\GatewayAwareInterface;
use Payum\Core\GatewayAwareTrait;
use Payum\Core\Request\Sync;
use Payum\Payex\Request\Api\CheckOrder;

class PaymentDetailsSyncAction implements ActionInterface, GatewayAwareInterface
{
    use GatewayAwareTrait;

    public function execute($request)
    {
        /** @var Sync $request */
        RequestNotSupportedException::assertSupports($this, $request);

        $model = ArrayObject::ensureArrayObject($request->getModel());

        if ($model['transactionNumber']) {
            $this->gateway->execute(new CheckOrder($request->getModel()));
        }
    }

    public function supports($request)
    {
        return $request instanceof Sync &&
            $request->getModel() instanceof \ArrayAccess &&
            $request->getModel()->offsetExists('transactionNumber')
        ;
    }
}
