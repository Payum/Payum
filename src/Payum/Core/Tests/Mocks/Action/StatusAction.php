<?php
namespace Payum\Core\Tests\Mocks\Action;

use Payum\Core\Action\ActionInterface;
use Payum\Core\Request\StatusRequestInterface;

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