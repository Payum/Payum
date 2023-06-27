<?php

namespace Payum\Core\Tests\Mocks\Action;

use Payum\Core\Action\ActionInterface;
use Payum\Core\Request\GetStatusInterface;

class StatusAction implements ActionInterface
{
    public function execute(mixed $request): void
    {
        $request->markCaptured();
    }

    public function supports(mixed $request): bool
    {
        return $request instanceof GetStatusInterface;
    }
}
