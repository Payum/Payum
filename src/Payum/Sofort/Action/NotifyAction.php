<?php

namespace Invit\PayumSofortueberweisung\Action;

use Payum\Core\Reply\HttpResponse;
use Payum\Core\Request\Notify;
use Payum\Core\Request\Sync;
use Payum\Core\Action\GatewayAwareAction;
use Payum\Core\Exception\RequestNotSupportedException;

class NotifyAction extends GatewayAwareAction
{
    /**
     * {@inheritdoc}
     */
    public function execute($request)
    {
        /* @var $request Notify */
        RequestNotSupportedException::assertSupports($this, $request);

        $this->gateway->execute(new Sync($request->getModel()));

        throw new HttpResponse('OK', 200);
    }

    /**
     * {@inheritdoc}
     */
    public function supports($request)
    {
        return
            $request instanceof Notify &&
            $request->getModel() instanceof \ArrayAccess
            ;
    }
}
