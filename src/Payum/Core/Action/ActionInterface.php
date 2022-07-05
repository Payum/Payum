<?php

namespace Payum\Core\Action;

use Payum\Core\Exception\RequestNotSupportedException;

interface ActionInterface
{
    /**
     * @param mixed $request
     *
     * @throws RequestNotSupportedException if the action dose not support the request.
     */
    public function execute($request);

    /**
     * @param mixed $request
     *
     * @return boolean
     */
    public function supports($request);
}
