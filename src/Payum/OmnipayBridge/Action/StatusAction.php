<?php
namespace Payum\OmnipayBridge\Action;

use Payum\Action\ActionInterface;
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

        $options = $request->getModel();
        if (false == isset($options['_status'])) {
            $request->markUnknown();
            
            return;
        }

        if ('success' === $options['_status']) {
            $request->markSuccess();
            
            return;
        }

        if ('failed' === $options['_status']) {
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
            $request->getModel() instanceof \ArrayObject
        ;
    }
}