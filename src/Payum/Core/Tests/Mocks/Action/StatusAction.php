<?php

namespace Payum\Core\Tests\Mocks\Action;

use Payum\Core\Action\ActionInterface;
use Payum\Core\Request\GetStatusInterface;

class StatusAction implements ActionInterface
{
    /**
     * @param GetStatusInterface $request
     */
    public function execute($request)
    {
        $request->markCaptured();
    }

    public function supports($request)
    {
        return $request instanceof GetStatusInterface;
    }
}
