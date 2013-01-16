<?php
namespace Payum\AuthorizeNet\Aim\Action;

use Payum\Domain\InstructionAggregateInterface;
use Payum\Domain\InstructionAwareInterface;
use Payum\Exception\LogicException;
use Payum\Request\CaptureRequest;
use Payum\Request\CreatePaymentInstructionRequest;
use Payum\Request\UserInputRequiredInteractiveRequest;
use Payum\Exception\RequestNotSupportedException;
use Payum\AuthorizeNet\Aim\PaymentInstruction;

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

        if (null == $request->getModel()->getInstruction()) {
            $this->payment->execute(new CreatePaymentInstructionRequest($request->getModel()));

            if (false == $request->getModel()->getInstruction() instanceof PaymentInstruction) {
                throw new LogicException('Create payment instruction request should set expected instruction to the model');
            }
        }

        /** @var $instruction PaymentInstruction */
        $instruction = $request->getModel()->getInstruction();
        if (false == $instruction->getResponseCode()) {
            if ($instruction->getAmount() && $instruction->getCardNum() && $instruction->getExpDate()) {
                $api = clone $this->payment->getApi();

                $instruction->fillRequest($api);
                $instruction->updateFromResponse($api->authorizeAndCapture());
            } else {
                throw new UserInputRequiredInteractiveRequest(array('amount', 'card_num', 'exp_date'));
            }
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