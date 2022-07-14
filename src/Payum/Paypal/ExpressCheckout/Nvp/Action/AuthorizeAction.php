<?php

namespace Payum\Paypal\ExpressCheckout\Nvp\Action;

use ArrayAccess;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Request\Authorize;
use Payum\Core\Request\Capture;
use Payum\Paypal\ExpressCheckout\Nvp\Api;

class AuthorizeAction extends PurchaseAction
{
    public function execute(mixed $request): void
    {
        /** @var Capture $request */
        RequestNotSupportedException::assertSupports($this, $request);

        $details = ArrayObject::ensureArrayObject($request->getModel());

        $details['PAYMENTREQUEST_0_PAYMENTACTION'] = Api::PAYMENTACTION_AUTHORIZATION;

        parent::execute($request);
    }

    public function supports(mixed $request): bool
    {
        return $request instanceof Authorize &&
            $request->getModel() instanceof ArrayAccess
        ;
    }
}
