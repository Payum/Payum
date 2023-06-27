<?php

namespace Payum\Sofort\Action;

use ArrayAccess;
use Payum\Core\Action\ActionInterface;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\GatewayAwareInterface;
use Payum\Core\GatewayAwareTrait;
use Payum\Core\Request\Refund;
use Payum\Core\Request\Sync;
use Payum\Sofort\Request\Api\RefundTransaction;

class RefundAction implements ActionInterface, GatewayAwareInterface
{
    use GatewayAwareTrait;

    public function execute(mixed $request): void
    {
        RequestNotSupportedException::assertSupports($this, $request);

        $this->gateway->execute(new RefundTransaction($request->getModel()));

        $this->gateway->execute(new Sync($request->getModel()));
    }

    public function supports(mixed $request): bool
    {
        return $request instanceof Refund &&
            $request->getModel() instanceof ArrayAccess
        ;
    }
}
