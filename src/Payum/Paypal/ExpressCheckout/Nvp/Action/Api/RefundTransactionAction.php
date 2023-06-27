<?php

namespace Payum\Paypal\ExpressCheckout\Nvp\Action\Api;

use ArrayAccess;
use Payum\Core\Action\ActionInterface;
use Payum\Core\ApiAwareInterface;
use Payum\Core\ApiAwareTrait;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Paypal\ExpressCheckout\Nvp\Api;
use Payum\Paypal\ExpressCheckout\Nvp\Request\Api\RefundTransaction;

class RefundTransactionAction implements ActionInterface, ApiAwareInterface
{
    use ApiAwareTrait;

    public function __construct()
    {
        $this->apiClass = Api::class;
    }

    public function execute(mixed $request): void
    {
        /** @var RefundTransaction $request */
        RequestNotSupportedException::assertSupports($this, $request);

        $model = ArrayObject::ensureArrayObject($request->getModel());
        $model->validateNotEmpty(['TRANSACTIONID']);

        $model->replace(
            $this->api->refundTransaction((array) $model)
        );
    }

    public function supports(mixed $request): bool
    {
        return $request instanceof RefundTransaction &&
            $request->getModel() instanceof ArrayAccess
        ;
    }
}
