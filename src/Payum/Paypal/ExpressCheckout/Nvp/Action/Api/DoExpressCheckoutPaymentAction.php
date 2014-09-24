<?php
namespace Payum\Paypal\ExpressCheckout\Nvp\Action\Api;

use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Exception\LogicException;
use Payum\Paypal\ExpressCheckout\Nvp\Request\Api\DoExpressCheckoutPayment;

class DoExpressCheckoutPaymentAction extends BaseApiAwareAction
{
    /**
     * {@inheritDoc}
     */
    public function execute($request)
    {
        /** @var $request DoExpressCheckoutPayment */
        RequestNotSupportedException::assertSupports($this, $request);

        $model = ArrayObject::ensureArrayObject($request->getModel());

        if (null === $model['TOKEN']) {
            throw new LogicException('TOKEN must be set. Have you run SetExpressCheckoutAction?');
        }
        if (null === $model['PAYERID']) {
            throw new LogicException('PAYERID must be set. Has user authorized this transaction?');
        }
        if (null === $model['PAYMENTREQUEST_0_PAYMENTACTION']) {
            throw new LogicException('PAYMENTREQUEST_0_PAYMENTACTION must be set.');
        }
        if (null === $model['PAYMENTREQUEST_0_AMT']) {
            throw new LogicException('PAYMENTREQUEST_0_AMT must be set.');
        }

        $model->replace(
            $this->api->doExpressCheckoutPayment((array) $model)
        );
    }

    /**
     * {@inheritDoc}
     */
    public function supports($request)
    {
        return
            $request instanceof DoExpressCheckoutPayment &&
            $request->getModel() instanceof \ArrayAccess
        ;
    }
}
