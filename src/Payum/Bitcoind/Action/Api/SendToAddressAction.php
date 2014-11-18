<?php
namespace Payum\Bitcoind\Action\Api;

use Payum\Core\Action\ActionInterface;
use Payum\Core\ApiAwareInterface;
use Payum\Core\Exception\UnsupportedApiException;

class SendToAddressAction implements  ActionInterface, ApiAwareInterface
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

    /**
     * @param mixed $api
     *
     * @throws UnsupportedApiException if the given Api is not supported.
     */
    public function setApi($api)
    {
        // TODO: Implement setApi() method.
    }
}