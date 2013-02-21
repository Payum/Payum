<?php
namespace Payum\AuthorizeNet\Aim\Action;

use Payum\Bridge\Spl\ArrayObject;
use Payum\Request\CaptureRequest;
use Payum\Request\UserInputRequiredInteractiveRequest;
use Payum\Exception\RequestNotSupportedException;

class CaptureAction extends ActionPaymentAware
{
    /**
     * {@inheritdoc}
     */
    public function execute($request)
    {
        /** @var $request CaptureRequest */
        if (false == $this->supports($request)) {
            throw RequestNotSupportedException::createActionNotSupported($this, $request);
        }

        $model = new ArrayObject($request->getModel());

        if (null != $model['response_code']) {
            return;
        }
        
        if (false == ($model['amount'] && $model['card_num'] && $model['exp_date'])) {
            throw new UserInputRequiredInteractiveRequest(array('amount', 'card_num', 'exp_date'));
        }
        
        $api = clone $this->payment->getApi();
        $api->ignore_not_x_fields = true;
        $api->setFields(array_filter((array) $model));

        $response = $api->authorizeAndCapture();

        $model->replace((array) $response);
    }

    /**
     * {@inheritdoc}
     */
    public function supports($request)
    {
        return 
            $request instanceof CaptureRequest &&
            $request->getModel() instanceof \ArrayAccess
        ;
    }
}