<?php
namespace Payum\Examples\Action;

use Payum\Action\ActionInterface;
use Payum\Request\StatusRequestInterface;

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
        return $request instanceof StatusRequestInterface;
    }
}