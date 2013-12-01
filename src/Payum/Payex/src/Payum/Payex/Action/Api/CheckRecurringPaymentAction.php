<?php
namespace Payum\Payex\Action\Api;

use Payum\Action\ActionInterface;
use Payum\ApiAwareInterface;
use Payum\Bridge\Spl\ArrayObject;
use Payum\Exception\RequestNotSupportedException;
use Payum\Exception\UnsupportedApiException;
use Payum\Payex\Api\RecurringApi;
use Payum\Payex\Request\Api\CheckRecurringPaymentRequest;

class CheckRecurringPaymentAction implements ActionInterface, ApiAwareInterface
{
    /**
     * @var RecurringApi
     */
    protected $api;
    
    /**
     * {@inheritDoc}
     */
    public function setApi($api)
    {
        if (false == $api instanceof RecurringApi) {
            throw new UnsupportedApiException('Expected api must be instance of RecurringApi.');
        }
        
        $this->api = $api;
    }
    
    /**
     * {@inheritDoc}
     */
    public function execute($request)
    {
        /** @var $request CheckRecurringPaymentRequest */
        if (false == $this->supports($request)) {
            throw RequestNotSupportedException::createActionNotSupported($this, $request);
        }

        $model = ArrayObject::ensureArrayObject($request->getModel());

        $model->validatedKeysSet(array(
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
            $request instanceof CheckRecurringPaymentRequest &&
            $request->getModel() instanceof \ArrayAccess
        ;
    }
}