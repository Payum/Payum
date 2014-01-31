<?php
namespace Payum\Klarna\Checkout\Action;

use Payum\Core\Action\ActionInterface;
use Payum\Core\Action\PaymentAwareAction;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Request\CaptureRequest;
use Payum\Klarna\Checkout\Constants;
use Payum\Klarna\Checkout\Request\Api\CreateOrderRequest;
use Payum\Klarna\Checkout\Request\Api\ShowSnippetInteractiveRequest;
use Payum\Klarna\Checkout\Request\Api\UpdateOrderRequest;

class CaptureAction extends PaymentAwareAction
{
    /**
     * {@inheritDoc}
     */
    public function execute($request)
    {
        /** @var $request \Payum\Core\Request\CaptureRequest */
        if (false == $this->supports($request)) {
            throw RequestNotSupportedException::createActionNotSupported($this, $request);
        }

        $model = ArrayObject::ensureArrayObject($request->getModel());

        if (false == $model['status']) {
            if ($model['location']) {
                $modifyOrderRequest = new UpdateOrderRequest($model);
            } else {
                $modifyOrderRequest = new CreateOrderRequest($model);
            }

            $this->payment->execute($modifyOrderRequest);
        }

        if (
            Constants::STATUS_CHECKOUT_INCOMPLETE == $model['status'] ||
            Constants::STATUS_CHECKOUT_COMPLETE == $model['status']
        ) {
            throw new ShowSnippetInteractiveRequest($model['gui']['snippet']);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function supports($request)
    {
        return
            $request instanceof CaptureRequest &&
            $request->getModel() instanceof \ArrayAccess
        ;
    }
}