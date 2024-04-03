<?php

namespace Payum\Sofort\Action;

use ArrayAccess;
use Payum\Core\Action\ActionInterface;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\GatewayAwareInterface;
use Payum\Core\GatewayAwareTrait;
use Payum\Core\Reply\HttpResponse;
use Payum\Core\Request\Notify;
use Payum\Core\Request\Sync;

class NotifyAction implements ActionInterface, GatewayAwareInterface
{
    use GatewayAwareTrait;

    /**
     * @param Notify $request
     */
    public function execute($request): void
    {
        RequestNotSupportedException::assertSupports($this, $request);

        $this->gateway->execute(new Sync($request->getModel()));

        throw new HttpResponse('OK', 200);
    }

    public function supports($request)
    {
        return $request instanceof Notify &&
            $request->getModel() instanceof ArrayAccess
        ;
    }
}
