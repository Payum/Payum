<?php

namespace Invit\PayumSofort\Action;

use Invit\PayumSofort\Request\Api\RefundTransaction;
use Payum\Core\Request\Notify;
use Payum\Core\Request\Refund;
use Payum\Core\Request\Sync;
use Payum\Core\Action\GatewayAwareAction;
use Payum\Core\Exception\RequestNotSupportedException;

class RefundAction extends GatewayAwareAction
{
    /**
     * {@inheritdoc}
     */
    public function execute($request)
    {
        /* @var $request Notify */
        RequestNotSupportedException::assertSupports($this, $request);

        $this->gateway->execute(new RefundTransaction($request->getModel()));

        $this->gateway->execute(new Sync($request->getModel()));
    }

    /**
     * {@inheritdoc}
     */
    public function supports($request)
    {
        return
            $request instanceof Refund &&
            $request->getModel() instanceof \ArrayAccess
            ;
    }
}
