<?php
namespace Payum\Paypal\ExpressCheckout\Nvp\Action\Api;

use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Exception\LogicException;
use Payum\Paypal\ExpressCheckout\Nvp\Request\Api\DoVoid;

class DoVoidAction extends BaseApiAwareAction
{
    /**
     * {@inheritdoc}
     */
    public function execute($request)
    {
        /** @var $request DoCapture */
        RequestNotSupportedException::assertSupports($this, $request);

        $model = ArrayObject::ensureArrayObject($request->getModel());

        if (null === $model['TRANSACTIONID']) {
            throw new LogicException('TRANSACTIONID must be set. Has user not authorized this transaction?');
        }

        $model->replace(
            $this->api->doVoid((array) $model)
        );
    }

    /**
     * {@inheritdoc}
     */
    public function supports($request)
    {
        return
            $request instanceof DoVoid &&
            $request->getModel() instanceof \ArrayAccess
        ;
    }
}
