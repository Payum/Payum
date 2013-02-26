<?php
namespace Payum\Paypal\ExpressCheckout\Nvp\Action;

use Buzz\Message\Form\FormRequest;

use Payum\Bridge\Spl\ArrayObject;
use Payum\Exception\RequestNotSupportedException;
use Payum\Exception\LogicException;
use Payum\Paypal\ExpressCheckout\Nvp\Api;
use Payum\Paypal\ExpressCheckout\Nvp\Request\SetExpressCheckoutRequest;

class SetExpressCheckoutAction extends BaseActionApiAware
{
    /**
     * {@inheritdoc}
     */
    public function execute($request)
    {
        /** @var $request SetExpressCheckoutRequest */
        if (false == $this->supports($request)) {
            throw RequestNotSupportedException::createActionNotSupported($this, $request);
        }
        
        $model = new ArrayObject($request->getModel());

        if (null === $model['PAYMENTREQUEST_0_AMT']) {
            throw new LogicException('The PAYMENTREQEUST_0_AMT must be set.');
        }

        $buzzRequest = new FormRequest;
        $buzzRequest->setFields((array) $model);
        
        $response = $this->api->setExpressCheckout($buzzRequest);
        
        $model->replace($response);
    }

    /**
     * {@inheritdoc}
     */
    public function supports($request)
    {
        return 
            $request instanceof SetExpressCheckoutRequest &&
            $request->getModel() instanceof \ArrayAccess
        ;
    }
}