<?php

namespace Payum\Paypal\ExpressCheckout\Nvp\Action;

use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Request\Authorize;
use Payum\Core\Request\Capture;
use Payum\Paypal\ExpressCheckout\Nvp\Api;

class AuthorizeAction extends PurchaseAction
{
    public function execute($request)
    {
        /** @var $request Capture */
        RequestNotSupportedException::assertSupports($this, $request);

        $details = ArrayObject::ensureArrayObject($request->getModel());

        $details['PAYMENTREQUEST_0_PAYMENTACTION'] = Api::PAYMENTACTION_AUTHORIZATION;

        parent::execute($request);
    }

    public function supports($request)
    {
        return $request instanceof Authorize &&
            $request->getModel() instanceof \ArrayAccess
        ;
    }
}
