<?php
namespace Payum\Core\Tests\Mocks\Action;

use Payum\Core\Action\ActionInterface;
use Payum\Core\Request\GetStatusInterface;

class StatusAction implements ActionInterface
{
    /**
     * {@inheritdoc}
     *
     * @param GetStatusInterface $request
     */
    public function execute($request)
    {
        $request->markCaptured();
    }

    /**
     * {@inheritdoc}
     */
    public function supports($request)
    {
        return $request instanceof GetStatusInterface;
    }
}