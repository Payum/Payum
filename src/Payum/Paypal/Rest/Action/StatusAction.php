<?php
/**
 * Created by PhpStorm.
 * User: skadabr
 * Date: 9/25/13
 * Time: 4:27 PM
 */
namespace Payum\Paypal\Rest\Action;
use PayPal\Api\Payment;
use Payum\Action\ActionInterface;
use Payum\Request\StatusRequestInterface;


class StatusAction implements ActionInterface
{

    /**
     * @param mixed $request
     *
     * @throws \Payum\Exception\RequestNotSupportedException if the action dose not support the request.
     *
     * @return void
     */
    function execute($request)
    {
        /** @var $request \Payum\Request\StatusRequestInterface */
        if (false == $this->supports($request)) {
            throw RequestNotSupportedException::createActionNotSupported($this, $request);
        }

        if (
            isset($request->getModel()->state) &&
            'approved' == $request->getModel()->state
        ) {
            $request->markSuccess();

            return;
        }

        if (
            isset($request->getModel()->state) &&
            'created' == $request->getModel()->state
        ) {
            $request->markNew();

            return;
        }

        if (
            false == isset($request->getModel()->id)
        ) {
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

        $model = $request->getModel();
        if (false == $model instanceof Payment) {
            return false;
        }

        return true;
    }
}