<?php
namespace Payum\Paypal\ExpressCheckout\Nvp\Action\Api;

use Buzz\Message\Form\FormRequest;

use Payum\Bridge\Spl\ArrayObject;
use Payum\Exception\RequestNotSupportedException;
use Payum\Exception\LogicException;
use Payum\Paypal\ExpressCheckout\Nvp\Action\Api\BaseApiAwareAction;
use Payum\Paypal\ExpressCheckout\Nvp\Api;
use Payum\Paypal\ExpressCheckout\Nvp\Request\Api\DoExpressCheckoutPaymentRequest;

class DoExpressCheckoutPaymentAction extends BaseApiAwareAction
{
    /**
     * {@inheritdoc}
     */
    public function execute($request)
    {
        /** @var $request \Payum\Paypal\ExpressCheckout\Nvp\Request\Api\DoExpressCheckoutPaymentRequest */
        if (false == $this->supports($request)) {
            throw RequestNotSupportedException::createActionNotSupported($this, $request);
        }

        $model = ArrayObject::ensureArrayObject($request->getModel());

        if (null === $model['TOKEN']) {
            throw new LogicException('TOKEN must be set. Have you run SetExpressCheckoutAction?');
        }
        if (null === $model['PAYERID']) {
            throw new LogicException('PAYERID must be set. Have user authorize this transaction?');
        }
        if (null === $model['PAYMENTREQUEST_0_PAYMENTACTION']) {
            throw new LogicException('PAYMENTREQUEST_0_PAYMENTACTION must be set.');
        }
        if (null === $model['PAYMENTREQUEST_0_AMT']) {
            throw new LogicException('PAYMENTREQUEST_0_AMT must be set.');
        }
        
        $buzzRequest = new FormRequest();
        $buzzRequest->setFields((array) $model);

        $response = $this->api->doExpressCheckoutPayment($buzzRequest);

        $model->replace($response);
    }

    /**
     * {@inheritdoc}
     */
    public function supports($request)
    {
        return 
            $request instanceof DoExpressCheckoutPaymentRequest &&
            $request->getModel() instanceof \ArrayAccess
        ;
    }
}