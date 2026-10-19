<?php

declare(strict_types=1);

namespace Payum\Core\Tests\Bc\Fixture;

use ArrayAccess;
use Payum\Core\Action\ActionInterface;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Request\GetStatusInterface;

final class AwkwardStatusAction implements ActionInterface
{
    public function execute($request): void
    {
        RequestNotSupportedException::assertSupports($this, $request);

        /** @var GetStatusInterface $request */
        $status = ArrayObject::ensureArrayObject($request->getModel())['status'];

        match ($status) {
            null => $request->markNew(),
            'pending' => $request->markPending(),
            'authorized' => $request->markAuthorized(),
            'captured' => $request->markCaptured(),
            'refunded' => $request->markRefunded(),
            default => $request->markUnknown(),
        };
    }

    public function supports($request): bool
    {
        return $request instanceof GetStatusInterface && $request->getModel() instanceof ArrayAccess;
    }
}
