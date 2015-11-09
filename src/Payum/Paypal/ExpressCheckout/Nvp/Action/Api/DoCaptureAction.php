<?php
namespace Payum\Paypal\ExpressCheckout\Nvp\Action\Api;

use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Exception\LogicException;
use Payum\Paypal\ExpressCheckout\Nvp\Request\Api\DoCapture;

class DoCaptureAction extends BaseApiAwareAction
{
    /**
     * {@inheritDoc}
     */
    public function execute($request)
    {
        /** @var $request DoCapture */
        RequestNotSupportedException::assertSupports($this, $request);

        $model = ArrayObject::ensureArrayObject($request->getModel());

        if (null === $model['AUTHORIZATIONID']) {
            if (null === $model['TRANSACTIONID']) {
                throw new LogicException('TRANSACTIONID or AUTHORIZATIONID must be set.');
            } else {
                $model['AUTHORIZATIONID'] = $model['TRANSACTIONID'];
            }
        }

        $model->validateNotEmpty(array('AMT', 'COMPLETETYPE'));

        $model->replace(
            $this->api->doCapture((array) $model)
        );
    }

    /**
     * {@inheritDoc}
     */
    public function supports($request)
    {
        return
            $request instanceof DoCapture &&
            $request->getModel() instanceof \ArrayAccess
        ;
    }
}
