<?php

declare(strict_types=1);

namespace Payum\Core\Tests\Bc\Fixture;

use ArrayAccess;
use Payum\Core\Action\ActionInterface;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Reply\HttpResponse;
use Payum\Core\Request\Notify;

final class AwkwardNotifyAction implements ActionInterface
{
    public function execute($request): void
    {
        RequestNotSupportedException::assertSupports($this, $request);

        /** @var Notify $request */
        $model = ArrayObject::ensureArrayObject($request->getModel());
        $model['notified'] = true;

        throw new HttpResponse('[accepted]', 200, [
            'X-Awkward' => 'yes',
        ]);
    }

    public function supports($request): bool
    {
        return $request instanceof Notify && $request->getModel() instanceof ArrayAccess;
    }
}
