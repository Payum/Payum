<?php
namespace Payum\Paypal\ExpressCheckout\Nvp\Action\Api;

use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Exception\LogicException;
use Payum\Paypal\ExpressCheckout\Nvp\Request\Api\SetExpressCheckout;

class SetExpressCheckoutAction extends BaseApiAwareAction
{
    /**
     * {@inheritDoc}
     */
    public function execute($request)
    {
        /** @var $request SetExpressCheckout */
        RequestNotSupportedException::assertSupports($this, $request);

        $model = ArrayObject::ensureArrayObject($request->getModel());

        if (null === $model['PAYMENTREQUEST_0_AMT']) {
            throw new LogicException('The PAYMENTREQUEST_0_AMT must be set.');
        }

        $model->replace(
            $this->api->setExpressCheckout((array) $model)
        );
    }

    /**
     * {@inheritDoc}
     */
    public function supports($request)
    {
        return
            $request instanceof SetExpressCheckout &&
            $request->getModel() instanceof \ArrayAccess
        ;
    }
}
