<?php
namespace Payum\Paypal\ExpressCheckout\Nvp\Action\Api;

use Buzz\Message\Form\FormRequest;

use Payum\Bridge\Spl\ArrayObject;
use Payum\Exception\RequestNotSupportedException;
use Payum\Paypal\ExpressCheckout\Nvp\Action\Api\BaseApiAwareAction;
use Payum\Paypal\ExpressCheckout\Nvp\Exception\Http\HttpResponseAckNotSuccessException;
use Payum\Paypal\ExpressCheckout\Nvp\Request\Api\CreateRecurringPaymentProfileRequest;
use Payum\Paypal\ExpressCheckout\Nvp\Request\Api\GetRecurringPaymentsProfileDetailsRequest;

class GetRecurringPaymentsProfileDetailsAction extends BaseApiAwareAction
{
    /**
     * {@inheritdoc}
     */
    public function execute($request)
    {
        /** @var $request CreateRecurringPaymentProfileRequest */
        if (false == $this->supports($request)) {
            throw RequestNotSupportedException::createActionNotSupported($this, $request);
        }

        $model = ArrayObject::ensureArrayObject($request->getModel());

        $model->validatedNotEmpty('PROFILEID');

        try {
            $buzzRequest = new FormRequest();
            $buzzRequest->setField('PROFILEID', $model['PROFILEID']);

            $response = $this->api->getRecurringPaymentsProfileDetails($buzzRequest);
            
            $model->replace($response);
        } catch (HttpResponseAckNotSuccessException $e) {
            $model->replace($e->getResponse());
        }
    }

    /**
     * {@inheritdoc}
     */
    public function supports($request)
    {
        return 
            $request instanceof GetRecurringPaymentsProfileDetailsRequest &&
            $request->getModel() instanceof \ArrayAccess
        ;
    }
}