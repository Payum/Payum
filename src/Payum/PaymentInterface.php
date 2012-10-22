<?php
namespace Payum;

use Payum\Action\ActionInterface;

interface PaymentInterface
{
    /**
     * @param ActionInterface $action
     * 
     * @return void
     */
    function addAction(ActionInterface $action);

    /**
     * @param mixed $request
     * 
     * @throws \Payum\Exception\RequestNotSupportedException if any action supports the request.
     * 
     * @return \Payum\Request\InteractiveRequestInterface|null
     */
    function execute($request);
}