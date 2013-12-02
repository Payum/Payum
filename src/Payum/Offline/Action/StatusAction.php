<?php
namespace Payum\Offline\Action;

use Payum\Core\Action\ActionInterface;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Offline\Constants;
use Payum\Core\Request\BinaryMaskStatusRequest;
use Payum\Core\Request\CaptureRequest;
use Payum\Core\Request\StatusRequestInterface;

class StatusAction implements ActionInterface
{
    /**
     * {@inheritDoc}
     */
    public function execute($request)
    {
        /** @var $request \Payum\Core\Request\StatusRequestInterface */
        if (false == $this->supports($request)) {
            throw RequestNotSupportedException::createActionNotSupported($this, $request);
        }

        $model = ArrayObject::ensureArrayObject($request->getModel());

        if (false == isset($model[Constants::FIELD_STATUS])) {
            $request->markNew();

            return;
        }

        if (Constants::STATUS_PENDING == $model[Constants::FIELD_STATUS]) {
            $request->markPending();

            return;
        }

        if (Constants::STATUS_SUCCESS == $model[Constants::FIELD_STATUS]) {
            $request->markSuccess();

            return;
        }

        $request->markUnknown();
    }

    /**
     * {@inheritDoc}
     */
    public function supports($request)
    {
        return
            $request instanceof StatusRequestInterface &&
            $request->getModel() instanceof \ArrayAccess
        ;
    }
}