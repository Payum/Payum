<?php
namespace Payum\Examples;

use Payum\Action\ActionInterface;
use Payum\Request\StatusRequestInterface;
use Payum\Request\SimpleSellRequest;

class StatusAction implements ActionInterface
{
    /**
     * {@inheritdoc}
     */
    public function execute($request)
    {
        $request->markSuccess();
    }

    /**
     * {@inheritdoc}
     */
    public function supports($request)
    {
        return 
            $request instanceof StatusRequestInterface && 
            $request->getRequest() instanceof SimpleSellRequest
        ;
    }
}