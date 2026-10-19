<?php

declare(strict_types=1);

namespace Payum\Core\Tests\Bc\Fixture;

use ArrayAccess;
use Payum\Core\Action\ActionInterface;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Reply\HttpRedirect;

final class AwkwardRedirectAction implements ActionInterface
{
    public function execute($request): void
    {
        RequestNotSupportedException::assertSupports($this, $request);

        throw new HttpRedirect('https://psp.test/pay/' . $request->getModel()['psp_ref']);
    }

    public function supports($request): bool
    {
        return $request instanceof AwkwardRedirect && $request->getModel() instanceof ArrayAccess;
    }
}
