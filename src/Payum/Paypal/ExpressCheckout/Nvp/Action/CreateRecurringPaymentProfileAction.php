<?php
namespace Payum\Paypal\ExpressCheckout\Nvp\Action;

use Buzz\Message\Form\FormRequest;
use Payum\Bridge\Spl\ArrayObject;
use Payum\Exception\LogicException;
use Payum\Exception\RequestNotSupportedException;
use Payum\Paypal\ExpressCheckout\Nvp\Exception\Http\HttpResponseAckNotSuccessException;
use Payum\Paypal\ExpressCheckout\Nvp\Request\CreateRecurringPaymentProfileRequest;

class CreateRecurringPaymentProfileAction extends BaseActionApiAware
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

        $model->validatedNotEmpty(array(
            'TOKEN',
            'PROFILESTARTDATE',
            'DESC',
            'BILLINGPERIOD',
            'BILLINGFREQUENCY',
            'AMT',
            'CURRENCYCODE',
            'EMAIL',
            'STREET',
            'CITY',
            'COUNTRYCODE',
            'ZIP',
        ));

        try {
            $buzzRequest = new FormRequest();
            $buzzRequest->setFields((array) $model);

            $response = $this->api->createRecurringPaymentsProfile($buzzRequest);
            
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
            $request instanceof CreateRecurringPaymentProfileRequest &&
            $request->getModel() instanceof \ArrayAccess
        ;
    }
}