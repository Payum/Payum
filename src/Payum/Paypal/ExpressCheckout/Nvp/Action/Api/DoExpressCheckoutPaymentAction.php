<?php

namespace Payum\Paypal\ExpressCheckout\Nvp\Action\Api;

use ArrayAccess;
use Payum\Core\Action\ActionInterface;
use Payum\Core\ApiAwareInterface;
use Payum\Core\ApiAwareTrait;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\LogicException;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Paypal\ExpressCheckout\Nvp\Api;
use Payum\Paypal\ExpressCheckout\Nvp\Request\Api\DoExpressCheckoutPayment;

class DoExpressCheckoutPaymentAction implements ActionInterface, ApiAwareInterface
{
    use ApiAwareTrait;

    public function __construct()
    {
        $this->apiClass = Api::class;
    }

    public function execute(mixed $request): void
    {
        /** @var DoExpressCheckoutPayment $request */
        RequestNotSupportedException::assertSupports($this, $request);

        $model = ArrayObject::ensureArrayObject($request->getModel());

        if (null === $model['TOKEN']) {
            throw new LogicException('TOKEN must be set. Have you run SetExpressCheckoutAction?');
        }
        if (null === $model['PAYERID']) {
            throw new LogicException('PAYERID must be set. Has user authorized this transaction?');
        }
        if (null === $model['PAYMENTREQUEST_0_PAYMENTACTION']) {
            throw new LogicException('PAYMENTREQUEST_0_PAYMENTACTION must be set.');
        }
        if (null === $model['PAYMENTREQUEST_0_AMT']) {
            throw new LogicException('PAYMENTREQUEST_0_AMT must be set.');
        }

        $model->replace(
            $this->api->doExpressCheckoutPayment((array) $model)
        );
    }

    public function supports(mixed $request): bool
    {
        return $request instanceof DoExpressCheckoutPayment &&
            $request->getModel() instanceof ArrayAccess
        ;
    }
}
