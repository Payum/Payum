<?php

namespace Payum\Payex\Action\Api;

use ArrayAccess;
use Payum\Core\Action\ActionInterface;
use Payum\Core\ApiAwareInterface;
use Payum\Core\ApiAwareTrait;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\LogicException;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Reply\HttpRedirect;
use Payum\Payex\Api\OrderApi;
use Payum\Payex\Request\Api\InitializeOrder;

class InitializeOrderAction implements ActionInterface, ApiAwareInterface
{
    use ApiAwareTrait;

    public function __construct()
    {
        $this->apiClass = OrderApi::class;
    }

    public function execute($request): void
    {
        /** @var InitializeOrder $request */
        RequestNotSupportedException::assertSupports($this, $request);

        $model = ArrayObject::ensureArrayObject($request->getModel());

        if ($model['orderRef']) {
            throw new LogicException('The order has already been initialized.');
        }

        $model->validatedKeysSet([
            'price',
            'priceArgList',
            'vat',
            'currency',
            'orderId',
            'productNumber',
            'purchaseOperation',
            'view',
            'description',
            'additionalValues',
            'returnUrl',
            'cancelUrl',
            'clientIPAddress',
            'clientIdentifier',
            'agreementRef',
            'clientLanguage',
        ]);

        $result = $this->api->initialize((array) $model);

        $model->replace($result);

        if ($model['redirectUrl']) {
            throw new HttpRedirect($model['redirectUrl']);
        }
    }

    public function supports($request)
    {
        return $request instanceof InitializeOrder &&
            $request->getModel() instanceof ArrayAccess
        ;
    }
}
