<?php
namespace Payum\Be2Bill\Action;

use Payum\Action\ActionPaymentAware;
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
     * @var \Payum\Be2Bill\Api
     */
    protected $api;

    /**
     * @param \Payum\Be2Bill\Api $api
     */
    public function __construct(Api $api)
    {
        $this->api = $api;
    }
    
    /**
     * {inheritdoc}
     */
    public function execute($request)
    {
        /** @var $request CaptureRequest */
        if (false == $this->supports($request)) {
            throw RequestNotSupportedException::createActionNotSupported($this, $request);
        }
        
        if (null == $request->getModel()->getInstruction()) {
            $this->payment->execute(new CreatePaymentInstructionRequest($request->getModel()));
            
            if (false == $request->getModel()->getInstruction() instanceof PaymentInstruction) {
                throw new LogicException('Create payment instruction request should set expected instruction to the model');
            }
        }
        
        /** @var $instruction PaymentInstruction */
        $instruction = $request->getModel()->getInstruction();
        
        if (null === $instruction->getExeccode()) {
            if (false == ($instruction->getCardcode() && $instruction->getCardcvv() && $instruction->getCardvaliditydate())) {
                throw new UserInputRequiredInteractiveRequest(array(
                    'cardcode',
                    'cardcvv',
                    'cardvaliditydate'
                ));
            }
            
            $response = $this->api->payment($instruction->toParams());
            
            $instruction->fromParams((array) $response->getContentJson());
        }
    }

    /**
     * {inheritdoc}
     */
    public function supports($request)
    {
        return 
            $request instanceof CaptureRequest &&
            $request->getModel() instanceof InstructionAwareInterface &&
            $request->getModel() instanceof InstructionAggregateInterface &&
            (
                null == $request->getModel()->getInstruction() ||
                $request->getModel()->getInstruction() instanceof PaymentInstruction
            )
        ;
    }
}
