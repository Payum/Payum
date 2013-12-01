<?php
namespace Payum\Bridge\Omnipay\Action;

use Payum\Action\ActionInterface;
use Payum\Bridge\Spl\ArrayObject;
use Payum\Exception\RequestNotSupportedException;
use Payum\Request\StatusRequestInterface;

class StatusAction implements ActionInterface
{
    /**
     * {@inheritdoc}
     */
    public function execute($request)
    {
        /** @var $request StatusRequestInterface */
        if (false == $this->supports($request)) {
            throw RequestNotSupportedException::createActionNotSupported($this, $request);
        }

        $model = ArrayObject::ensureArrayObject($request->getModel());

        if (null === $model['_status']) {
            $request->markNew();
            
            return;
        }

        if ('success' === $model['_status']) {
            $request->markSuccess();
            
            return;
        }

        if ('failed' === $model['_status']) {
            $request->markFailed();

            return;
        }

        $request->markUnknown();
    }

    /**
     * {@inheritdoc}
     */
    public function supports($request)
    {
        return 
            $request instanceof StatusRequestInterface &&
            $request->getModel() instanceof \ArrayAccess
        ;
    }
}