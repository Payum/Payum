<?php

namespace Payum\Paypal\ExpressCheckout\Nvp\Action\Api;

use Payum\Core\Action\ActionInterface;
use Payum\Core\ApiAwareInterface;
use Payum\Core\ApiAwareTrait;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\LogicException;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Paypal\ExpressCheckout\Nvp\Api;
use Payum\Paypal\ExpressCheckout\Nvp\Request\Api\DoReferenceTransaction;

class DoReferenceTransactionAction implements ActionInterface, ApiAwareInterface
{
    use ApiAwareTrait;

    public function __construct()
    {
        $this->apiClass = Api::class;
    }

    /**
     * {@inheritDoc}
     */
    public function execute($request)
    {
        /** @var $request DoReferenceTransaction */
        RequestNotSupportedException::assertSupports($this, $request);

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

        $model->replace(
            $this->api->doReferenceTransaction((array) $model)
        );
    }

    /**
     * {@inheritDoc}
     */
    public function supports($request)
    {
        return $request instanceof DoReferenceTransaction &&
            $request->getModel() instanceof \ArrayAccess
        ;
    }
}
