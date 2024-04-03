<?php

namespace Payum\Klarna\Invoice\Action;

use ArrayAccess;
use Payum\Core\Action\ActionInterface;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\GatewayAwareInterface;
use Payum\Core\GatewayAwareTrait;
use Payum\Core\Request\Authorize;
use Payum\Klarna\Invoice\Request\Api\ReserveAmount;

class AuthorizeAction implements ActionInterface, GatewayAwareInterface
{
    use GatewayAwareTrait;

    /**
     * @param Authorize $request
     */
    public function execute($request): void
    {
        RequestNotSupportedException::assertSupports($this, $request);

        $details = ArrayObject::ensureArrayObject($request->getModel());

        if (! $details['rno']) {
            $this->gateway->execute(new ReserveAmount($details));
        }
    }

    public function supports($request)
    {
        return $request instanceof Authorize &&
            $request->getModel() instanceof ArrayAccess
        ;
    }
}
