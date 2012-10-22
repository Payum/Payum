<?php
namespace Payum\Action;

interface ActionInterface
{
    /**
     * @param mixed $request
     * 
     * @throws \Payum\Exception\RequestNotSupportedException if the action dose not support the request.
     * 
     * @return void
     */
    function execute($request);

    /**
     * @param mixed $request
     * 
     * @return boolean
     */
    function supports($request);
}