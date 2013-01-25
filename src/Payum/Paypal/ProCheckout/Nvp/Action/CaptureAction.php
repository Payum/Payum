<?php
namespace Payum\Paypal\ProCheckout\Nvp\Action;


class CaptureAction extends ActionPaymentAware
{
    public function execute($request)
    {
        /** @var $request CaptureRequest */
        if (false == $this->supports($request)) {
            throw RequestNotSupportedException::createActionNotSupported($this, $request);
        }

        if (null === $request->getModel()->getInstruction()) {
            $this->payment->execute(new CreatePaymentInstructionRequest($request->getModel()));

            if (false == $request->getModel()->getInstruction() instanceof PaymentInstruction) {
                throw new LogicException('Create payment instruction request should set expected instruction to the model');
            }
        }

        try {

            /** @var $instruction PaymentInstruction */
            $instruction = $request->getModel()->getInstruction();
            $instruction->setPaymentrequestPaymentaction(0, Api::PAYMENTACTION_SALE);
            if (false == $instruction->getToken()) {
                $this->payment->execute(new SetExpressCheckoutRequest($instruction));
                $this->payment->execute(new AuthorizeTokenRequest($instruction));
            }

            $this->payment->execute(new SyncRequest($instruction));
            
            if (
                $instruction->getPayerid() &&  
                Api::CHECKOUTSTATUS_PAYMENT_ACTION_NOT_INITIATED == $instruction->getCheckoutstatus()
            ) {
                $this->payment->execute(new DoExpressCheckoutPaymentRequest($instruction));
            }

            $this->payment->execute(new SyncRequest($instruction));
        } catch (HttpResponseAckNotSuccessException $e) {
            $instruction->clearErrors();
            $instruction->fromNvp($e->getResponse());
        }
    }

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
