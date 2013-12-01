<?php
namespace Payum\Payex\Action\Api;

use Payum\Action\ActionInterface;
use Payum\ApiAwareInterface;
use Payum\Bridge\Spl\ArrayObject;
use Payum\Exception\LogicException;
use Payum\Exception\RequestNotSupportedException;
use Payum\Exception\UnsupportedApiException;
use Payum\Payex\Api\OrderApi;
use Payum\Payex\Request\Api\InitializeOrderRequest;
use Payum\Request\RedirectUrlInteractiveRequest;

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
        /** @var $request InitializeOrderRequest */
        if (false == $this->supports($request)) {
            throw RequestNotSupportedException::createActionNotSupported($this, $request);
        }

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
            throw new RedirectUrlInteractiveRequest($model['redirectUrl']);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function supports($request)
    {
        return 
            $request instanceof InitializeOrderRequest &&
            $request->getModel() instanceof \ArrayAccess
        ;
    }
}