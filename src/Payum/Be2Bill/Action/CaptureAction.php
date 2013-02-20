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
        if (null === $model['EXECCODE']) {
            //instruction must have an alias set (e.g oneclick payment) or credit card info. 
            if ($model['ALIAS'] ||
                ($model->offsetsExists(array('CARDCODE', 'CARDCVV', 'CARDVALIDITYDATE', 'CARDFULLNAME')))
            ) {
                $response = $this->payment->getApi()->payment((array) $model);

                $model->replace($response->getContentJson());
                
                if (false == is_object($request->getModel())) {
                    $request->setModel($model);
                }
            } else {
                throw new UserInputRequiredInteractiveRequest(array(
                    'cardcode',
                    'cardcvv',
                    'cardvaliditydate',
                    'cardfullname'
                ));
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function supports($request)
    {
        return 
            $request instanceof CaptureRequest &&
            (
                is_array($request->getModel()) || 
                $request->getModel() instanceof \ArrayAccess
            )
        ;
    }
}