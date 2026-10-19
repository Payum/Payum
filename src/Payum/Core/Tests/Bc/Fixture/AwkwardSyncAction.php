<?php

declare(strict_types=1);

namespace Payum\Core\Tests\Bc\Fixture;

use ArrayAccess;
use Payum\Core\Action\ActionInterface;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Request\Sync;

final class AwkwardSyncAction implements ActionInterface
{
    public function execute($request): void
    {
        RequestNotSupportedException::assertSupports($this, $request);

        /** @var Sync $request */
        $model = ArrayObject::ensureArrayObject($request->getModel());
        $model['synced'] = true;

        if ('pending' === $model['status']) {
            $model['status'] = 'captured';
        }
    }

    public function supports($request): bool
    {
        return $request instanceof Sync && $request->getModel() instanceof ArrayAccess;
    }
}
