<?php

namespace Payum\Sofort\Action;

use Payum\Core\Action\ActionInterface;
use Payum\Core\GatewayAwareInterface;
use Payum\Core\GatewayAwareTrait;
use Payum\Sofort\Request\Api\RefundTransaction;
use Payum\Core\Request\Notify;
use Payum\Core\Request\Refund;
use Payum\Core\Request\Sync;
use Payum\Core\Exception\RequestNotSupportedException;

class RefundAction implements ActionInterface, GatewayAwareInterface
{
    use GatewayAwareTrait;

    /**
     * {@inheritdoc}
     *
     * @param $request Notify
     */
    public function execute($request)
    {
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
