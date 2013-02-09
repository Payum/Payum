<?php
namespace Payum\Be2Bill\Action;

use Payum\Request\CaptureRequest;
use Payum\Request\CreatePaymentInstructionRequest;
use Payum\Request\UserInputRequiredInteractiveRequest;
use Payum\Domain\InstructionAwareInterface;
use Payum\Domain\InstructionAggregateInterface;
use Payum\Exception\RequestNotSupportedException;
use Payum\Exception\LogicException;
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
        
        /** @var $instruction PaymentInstruction */
        $instruction = $request->getModel();
        
        if (null === $instruction->getExeccode()) {
            //instruction must have an alias set (e.g oneclick payment) or credit card info. 
            if ($instruction->getAlias() ||
                ($instruction->getCardcode() && $instruction->getCardcvv() && $instruction->getCardvaliditydate())
            ) {
                $response = $this->payment->getApi()->payment($instruction->toParams());

                $instruction->fromParams((array) $response->getContentJson());
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
            $request->getModel() instanceof PaymentInstruction
        ;
    }
}
