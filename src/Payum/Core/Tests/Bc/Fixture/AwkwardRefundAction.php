<?php

declare(strict_types=1);

namespace Payum\Core\Tests\Bc\Fixture;

use ArrayAccess;
use Payum\Core\Action\ActionInterface;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Request\Refund;

/**
 * Matches on a details array only, so Refund($payment) reaches it through
 * ExecuteSameRequestWithModelDetailsAction. Answers with a reply of its own after changing state.
 */
final class AwkwardRefundAction implements ActionInterface
{
    public function execute($request): void
    {
        RequestNotSupportedException::assertSupports($this, $request);

        /** @var Refund $request */
        $model = ArrayObject::ensureArrayObject($request->getModel());
        $model['status'] = 'refunded';

        throw new AwkwardReply([
            'refunded' => $model['amount'],
        ]);
    }

    public function supports($request): bool
    {
        return $request instanceof Refund && $request->getModel() instanceof ArrayAccess;
    }
}
