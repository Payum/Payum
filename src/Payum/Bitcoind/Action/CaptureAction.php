<?php
namespace Payum\Bitcoind\Action;

use Payum\Core\Action\PaymentAwareAction;

class CaptureAction extends PaymentAwareAction
{

    /**
     * @param mixed $request
     *
     * @throws \Payum\Core\Exception\RequestNotSupportedException if the action dose not support the request.
     */
    function execute($request)
    {
        // TODO: Implement execute() method.
    }

    /**
     * @param mixed $request
     *
     * @return boolean
     */
    function supports($request)
    {
        // TODO: Implement supports() method.
    }
}