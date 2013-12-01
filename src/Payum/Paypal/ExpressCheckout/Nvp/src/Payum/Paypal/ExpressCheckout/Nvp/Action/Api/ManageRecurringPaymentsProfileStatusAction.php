<?php
namespace Payum\Paypal\ExpressCheckout\Nvp\Action\Api;

use Buzz\Message\Form\FormRequest;

use Payum\Bridge\Spl\ArrayObject;
use Payum\Exception\RequestNotSupportedException;
use Payum\Paypal\ExpressCheckout\Nvp\Exception\Http\HttpResponseAckNotSuccessException;
use Payum\Paypal\ExpressCheckout\Nvp\Request\Api\ManageRecurringPaymentsProfileStatusRequest;

class ManageRecurringPaymentsProfileStatusAction extends BaseApiAwareAction 
{
    /**
     * [@inheritdoc}
     */
    public function execute($request)
    {
        /** @var $request \Payum\Paypal\ExpressCheckout\Nvp\Request\Api\ManageRecurringPaymentsProfileStatusRequest */
        if (false == $this->supports($request)) {
            throw RequestNotSupportedException::createActionNotSupported($this, $request);
        }

        $model = ArrayObject::ensureArrayObject($request->getModel());

        $model->validatedNotEmpty(array('PROFILEID', 'ACTION'));

        $buzzRequest = new FormRequest;
        $buzzRequest->setFields((array) $model);

        try {
            $response = $this->api->manageRecurringPaymentsProfileStatus($buzzRequest);

            $model->replace($response);
        } catch (HttpResponseAckNotSuccessException $e) {
            $model->replace($e->getResponse());
        }
    }

    /**
     * [@inheritdoc}
     */
    public function supports($request)
    {
        return
            $request instanceof ManageRecurringPaymentsProfileStatusRequest &&
            $request->getModel() instanceof \ArrayAccess
        ;
    }
}