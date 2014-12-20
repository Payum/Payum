<?php
namespace Payum\Core\Tests\Mocks\Action;

use Payum\Core\Action\ActionInterface;
use Payum\Core\Request\GetStatusInterface;

class StatusAction implements ActionInterface
{
    /**
     * {@inheritDoc}
     *
     * @param GetStatusInterface $request
     */
    public function execute($request)
    {
        $request->markCaptured();
    }

    /**
     * {@inheritDoc}
     */
    public function supports($request)
    {
        return $request instanceof GetStatusInterface;
    }
}
