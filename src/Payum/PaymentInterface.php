<?php
namespace Payum;

use Payum\Action\ActionInterface;

interface PaymentInterface
{
    /**
     * @param mixed $api
     *
     * @return void
     */
    function addApi($api);
    
    /**
     * @param ActionInterface $action
     * 
     * @return void
     */
    function addAction(ActionInterface $action);

    /**
     * @param mixed $request
     * @param boolean $isInteractiveRequestExpected
     * 
     * @throws \Payum\Exception\RequestNotSupportedException if any action supports the request.
     * 
     * @return \Payum\Request\InteractiveRequestInterface|null
     */
    function execute($request, $isInteractiveRequestExpected = false);
}