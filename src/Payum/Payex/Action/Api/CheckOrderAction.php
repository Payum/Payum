<?php

namespace Payum\Payex\Action\Api;

use ArrayAccess;
use Payum\Core\Action\ActionInterface;
use Payum\Core\ApiAwareInterface;
use Payum\Core\ApiAwareTrait;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Payex\Api\OrderApi;
use Payum\Payex\Request\Api\CheckOrder;

class CheckOrderAction implements ActionInterface, ApiAwareInterface
{
    use ApiAwareTrait;

    public function __construct()
    {
        $this->apiClass = OrderApi::class;
    }

    public function execute(mixed $request): void
    {
        /** @var CheckOrder $request */
        RequestNotSupportedException::assertSupports($this, $request);

        $model = ArrayObject::ensureArrayObject($request->getModel());

        $model->validateNotEmpty([
            'transactionNumber',
        ]);

        $result = $this->api->check((array) $model);

        $model->replace($result);
    }

    public function supports(mixed $request): bool
    {
        return $request instanceof CheckOrder &&
            $request->getModel() instanceof ArrayAccess
        ;
    }
}
