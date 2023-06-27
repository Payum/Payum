<?php

namespace Payum\Stripe\Action;

use ArrayAccess;
use Payum\Core\Action\ActionInterface;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Request\GetCreditCardToken;

class GetCreditCardTokenAction implements ActionInterface
{
    public function execute(mixed $request): void
    {
        RequestNotSupportedException::assertSupports($this, $request);

        $model = ArrayObject::ensureArrayObject($request->getModel());

        $request->token = $model['customer'];
    }

    public function supports(mixed $request): bool
    {
        return $request instanceof GetCreditCardToken &&
            $request->getModel() instanceof ArrayAccess
        ;
    }
}
