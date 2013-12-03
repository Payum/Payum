<?php
namespace Payum\AuthorizeNet\Aim\Action;

use Payum\Core\Action\ActionInterface;
use Payum\Core\Request\StatusRequestInterface;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;

class StatusAction implements \Payum\Core\Action\ActionInterface
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

        if (null === $model['response_code']) {
            $request->markNew();

            return;
        }
        
        if (\AuthorizeNetAIM_Response::APPROVED == $model['response_code']) {
            $request->markSuccess();
            
            return;
        }

        if (\AuthorizeNetAIM_Response::DECLINED == $model['response_code']) {
            $request->markCanceled();

            return;
        }

        if (\AuthorizeNetAIM_Response::ERROR == $model['response_code']) {
            $request->markFailed();

            return;
        }

        if (\AuthorizeNetAIM_Response::HELD == $model['response_code']) {
            $request->markPending();

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