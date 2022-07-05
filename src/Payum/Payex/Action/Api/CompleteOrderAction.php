<?php

namespace Payum\Payex\Action\Api;

use ArrayAccess;
use Payum\Core\Action\ActionInterface;
use Payum\Core\ApiAwareInterface;
use Payum\Core\ApiAwareTrait;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Payex\Api\OrderApi;
use Payum\Payex\Request\Api\CompleteOrder;

class CompleteOrderAction implements ActionInterface, ApiAwareInterface
{
    use ApiAwareTrait;

    public function __construct()
    {
        $this->apiClass = OrderApi::class;
    }

    public function execute($request)
    {
        /** @var CompleteOrder $request */
        RequestNotSupportedException::assertSupports($this, $request);

        $model = ArrayObject::ensureArrayObject($request->getModel());

        $model->validateNotEmpty([
            'orderRef',
        ]);

        $result = $this->api->complete((array) $model);

        $model->replace($result);
    }

    public function supports($request)
    {
        return $request instanceof CompleteOrder &&
            $request->getModel() instanceof ArrayAccess
        ;
    }
}
