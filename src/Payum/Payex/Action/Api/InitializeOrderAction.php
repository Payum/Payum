<?php
namespace Payum\Payex\Action\Api;

use Payum\Core\Action\ActionInterface;
use Payum\Core\ApiAwareInterface;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\LogicException;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Exception\UnsupportedApiException;
use Payum\Core\Reply\HttpRedirect;
use Payum\Payex\Api\OrderApi;
use Payum\Payex\Request\Api\InitializeOrder;

class InitializeOrderAction implements ActionInterface, ApiAwareInterface
{
    /**
     * @var OrderApi
     */
    protected $api;

    /**
     * {@inheritDoc}
     */
    public function setApi($api)
    {
        if (false == $api instanceof OrderApi) {
            throw new UnsupportedApiException('Expected api must be instance of OrderApi.');
        }

        $this->api = $api;
    }

    /**
     * {@inheritDoc}
     */
    public function execute($request)
    {
        /** @var $request InitializeOrder */
        RequestNotSupportedException::assertSupports($this, $request);

        $model = ArrayObject::ensureArrayObject($request->getModel());

        if ($model['orderRef']) {
            throw new LogicException('The order has already been initialized.');
        }

        $model->validatedKeysSet(array(
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
        ));

        $result = $this->api->initialize((array) $model);

        $model->replace($result);

        if ($model['redirectUrl']) {
            throw new HttpRedirect($model['redirectUrl']);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function supports($request)
    {
        return
            $request instanceof InitializeOrder &&
            $request->getModel() instanceof \ArrayAccess
        ;
    }
}
