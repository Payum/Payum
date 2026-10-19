<?php

declare(strict_types=1);

namespace Payum\Core\Tests\Bc\Fixture;

use ArrayAccess;
use Payum\Core\Action\ActionInterface;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\GatewayAwareInterface;
use Payum\Core\GatewayAwareTrait;
use Payum\Core\Request\Authorize;
use Payum\Core\Request\ObtainCreditCard;

final class AwkwardAuthorizeAction implements ActionInterface, GatewayAwareInterface
{
    use GatewayAwareTrait;

    public function execute($request): void
    {
        RequestNotSupportedException::assertSupports($this, $request);

        /** @var Authorize $request */
        $model = ArrayObject::ensureArrayObject($request->getModel());

        try {
            $this->gateway->execute($obtain = new ObtainCreditCard());
            $model['card'] = $obtain->obtain()->getMaskedNumber();
        } catch (RequestNotSupportedException) {
            $model['card'] = 'nobody answered ObtainCreditCard';
        }

        $model['status'] = 'authorized';
    }

    public function supports($request): bool
    {
        return $request instanceof Authorize && $request->getModel() instanceof ArrayAccess;
    }
}
