<?php
namespace Payum\Paypal\ExpressCheckout\Nvp\Action\Api;

use Buzz\Message\Form\FormRequest;

use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Exception\LogicException;
use Payum\Paypal\ExpressCheckout\Nvp\Request\Api\DoReferenceTransactionRequest;

class DoReferenceTransactionAction extends BaseApiAwareAction
{
    /**
     * {@inheritdoc}
     */
    public function execute($request)
    {
        /** @var $request DoReferenceTransactionRequest */
        if (false == $this->supports($request)) {
            throw RequestNotSupportedException::createActionNotSupported($this, $request);
        }

        $model = ArrayObject::ensureArrayObject($request->getModel());

        if (null === $model['REFERENCEID']) {
            throw new LogicException('REFERENCEID must be set.');
        }
        if (null === $model['PAYMENTACTION']) {
            throw new LogicException('PAYMENTACTION must be set.');
        }
        if (null === $model['AMT']) {
            throw new LogicException('AMT must be set.');
        }
        
        $buzzRequest = new FormRequest();
        $buzzRequest->setFields((array) $model);

        $response = $this->api->doReferenceTransaction($buzzRequest);

        $model->replace($response);
    }

    /**
     * {@inheritdoc}
     */
    public function supports($request)
    {
        return 
            $request instanceof DoReferenceTransactionRequest &&
            $request->getModel() instanceof \ArrayAccess
        ;
    }
}