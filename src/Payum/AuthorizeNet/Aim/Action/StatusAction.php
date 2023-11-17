<?php

namespace Payum\AuthorizeNet\Aim\Action;

use ArrayAccess;
use AuthorizeNetAIM_Response;
use Payum\Core\Action\ActionInterface;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Request\GetStatusInterface;

class StatusAction implements ActionInterface
{
    /**
     * @param GetStatusInterface $request
     */
    public function execute($request): void
    {
        RequestNotSupportedException::assertSupports($this, $request);

        $model = ArrayObject::ensureArrayObject($request->getModel());

        if (null === $model['response_code']) {
            $request->markNew();

            return;
        }

        if (AuthorizeNetAIM_Response::APPROVED === $model['response_code']) {
            $request->markCaptured();

            return;
        }

        if (AuthorizeNetAIM_Response::DECLINED === $model['response_code']) {
            $request->markCanceled();

            return;
        }

        if (AuthorizeNetAIM_Response::ERROR === $model['response_code']) {
            $request->markFailed();

            return;
        }

        if (AuthorizeNetAIM_Response::HELD === $model['response_code']) {
            $request->markPending();

            return;
        }

        $request->markUnknown();
    }

    public function supports($request)
    {
        return $request instanceof GetStatusInterface &&
            $request->getModel() instanceof ArrayAccess
        ;
    }
}
