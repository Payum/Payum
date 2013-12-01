<?php
namespace Payum\Payex\Action\Api;

use Payum\Action\ActionInterface;
use Payum\ApiAwareInterface;
use Payum\Bridge\Spl\ArrayObject;
use Payum\Exception\RequestNotSupportedException;
use Payum\Exception\UnsupportedApiException;
use Payum\Payex\Api\OrderApi;
use Payum\Payex\Request\Api\CompleteOrderRequest;

class CompleteOrderAction implements ActionInterface, ApiAwareInterface
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
        /** @var $request CompleteOrderRequest */
        if (false == $this->supports($request)) {
            throw RequestNotSupportedException::createActionNotSupported($this, $request);
        }

        $model = ArrayObject::ensureArrayObject($request->getModel());

        $model->validatedNotEmpty(array(
            'orderRef',
        ));
        
        $result = $this->api->complete((array) $model);

        $model->replace($result);
    }

    /**
     * {@inheritDoc}
     */
    public function supports($request)
    {
        return 
            $request instanceof CompleteOrderRequest &&
            $request->getModel() instanceof \ArrayAccess
        ;
    }
}