<?php
namespace Payum\Payex\Action\Api;

use Payum\Action\ActionInterface;
use Payum\ApiAwareInterface;
use Payum\Bridge\Spl\ArrayObject;
use Payum\Payex\Api\AgreementApi;
use Payum\Exception\LogicException;
use Payum\Exception\RequestNotSupportedException;
use Payum\Exception\UnsupportedApiException;
use Payum\Payex\Request\Api\CheckAgreementRequest;
use Payum\Payex\Request\Api\CompleteOrderRequest;
use Payum\Payex\Request\Api\CreateAgreementRequest;

class CheckAgreementAction implements ActionInterface, ApiAwareInterface
{
    /**
     * @var AgreementApi
     */
    protected $api;
    
    /**
     * {@inheritDoc}
     */
    public function setApi($api)
    {
        if (false == $api instanceof AgreementApi) {
            throw new UnsupportedApiException('Expected api must be instance of AgreementApi.');
        }
        
        $this->api = $api;
    }
    
    /**
     * {@inheritDoc}
     */
    public function execute($request)
    {
        /** @var $request CheckAgreementRequest */
        if (false == $this->supports($request)) {
            throw RequestNotSupportedException::createActionNotSupported($this, $request);
        }

        $model = ArrayObject::ensureArrayObject($request->getModel());

        $model->validatedNotEmpty(array(
            'agreementRef',
        ));

        $result = $this->api->check((array) $model);

        $model->replace($result);
    }

    /**
     * {@inheritDoc}
     */
    public function supports($request)
    {
        return 
            $request instanceof CheckAgreementRequest &&
            $request->getModel() instanceof \ArrayAccess
        ;
    }
}