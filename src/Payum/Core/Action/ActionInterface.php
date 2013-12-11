<?php
namespace Payum\Core\Action;

interface ActionInterface
{
    /**
     * @param mixed $request
     * 
     * @throws \Payum\Core\Exception\RequestNotSupportedException if the action dose not support the request.
     */
    function execute($request);

    /**
     * @param mixed $request
     * 
     * @return boolean
     */
    function supports($request);
}