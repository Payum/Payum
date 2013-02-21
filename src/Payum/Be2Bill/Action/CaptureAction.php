<?php
namespace Payum\Be2Bill\Action;

use Payum\Bridge\Spl\ArrayObject;
use Payum\PaymentInstructionAggregateInterface;
use Payum\PaymentInstructionAwareInterface;
use Payum\Request\CaptureRequest;
use Payum\Request\UserInputRequiredInteractiveRequest;
use Payum\Exception\RequestNotSupportedException;
use Payum\Be2Bill\PaymentInstruction;
use Payum\Be2Bill\Api;

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
        
        if (null !== $model['EXECCODE']) {
            return;
        }

        //instruction must have an alias set (e.g oneclick payment) or credit card info. 
        if (false == (
            $model['ALIAS'] ||
            $model->offsetsExists(array('CARDCODE', 'CARDCVV', 'CARDVALIDITYDATE', 'CARDFULLNAME'))
        )) {
            throw new UserInputRequiredInteractiveRequest(array('CARDCODE', 'CARDCVV', 'CARDVALIDITYDATE', 'CARDFULLNAME'));
        }

        $response = $this->payment->getApi()->payment((array) $model);

        $model->replace($response->getContentJson());
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