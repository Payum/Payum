<?php

namespace Payum\Offline\Action;

use Payum\Core\Action\ActionInterface;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Request\Authorize;
use Payum\Core\Request\Capture;
use Payum\Offline\Constants;

class AuthorizeAction implements ActionInterface
{
    public function execute($request)
    {
        /** @var Capture $request */
        RequestNotSupportedException::assertSupports($this, $request);

        $model = ArrayObject::ensureArrayObject($request->getModel());

        if ($model[Constants::FIELD_PAID]) {
            $model[Constants::FIELD_STATUS] = Constants::STATUS_AUTHORIZED;
        } else {
            $model[Constants::FIELD_STATUS] = Constants::STATUS_PENDING;
        }
    }

    public function supports($request)
    {
        return $request instanceof Authorize &&
            $request->getModel() instanceof \ArrayAccess
        ;
    }
}
