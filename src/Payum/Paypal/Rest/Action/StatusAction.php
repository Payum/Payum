<?php

namespace Payum\Paypal\Rest\Action;

use PayPal\Api\Payment;
use Payum\Core\Action\ActionInterface;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Request\StatusRequestInterface;

class StatusAction implements ActionInterface
{
    /**
     * @param mixed $request
     *
     * @throws \Payum\Core\Exception\RequestNotSupportedException if the action dose not support the request.
     *
     * @return void
     */
    function execute($request)
    {
        /**
         * @var $request \Payum\Core\Request\StatusRequestInterface
         */
        if (false == $this->supports($request)) {
            throw RequestNotSupportedException::createActionNotSupported($this, $request);
        }

        /** @var Payment $model */
        $model = $request->getModel();

        if (isset($model->state) && 'approved' == $model->state ) {
            $request->markSuccess();

            return;
        }

        if (isset($model->state) && 'created' == $model->state ) {
            $request->markNew();

            return;
        }

        if (false == isset($model->state) ) {
            $request->markNew();

            return;
        }

        $request->markUnknown();
    }

    /**
     * @param mixed $request
     *
     * @return boolean
     */
    function supports($request)
    {
        if (false == $request instanceof StatusRequestInterface) {
            return false;
        }

        /** @var Payment $model */
        $model = $request->getModel();
        if (false == $model instanceof Payment) {
            return false;
        }

        return true;
    }
}