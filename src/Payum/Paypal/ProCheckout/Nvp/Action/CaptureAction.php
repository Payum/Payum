<?php
namespace Payum\Paypal\ProCheckout\Nvp\Action;

use Payum\Action\ActionPaymentAware;
use Payum\Exception\Http\HttpException;
use Payum\Request\CreatePaymentInstructionRequest;
use Payum\Paypal\ProCheckout\Nvp\Bridge\Buzz\Request;
use Payum\Paypal\ProCheckout\Nvp\PaymentInstruction;
use Payum\Domain\InstructionAggregateInterface;
use Payum\Domain\InstructionAwareInterface;
use Payum\Request\CaptureRequest;

/**
 * @author Ton Sharp <Forma-PRO@66ton99.org.ua>
 */
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


        /** @var $instruction PaymentInstruction */
        $instruction = $request->getModel()->getInstruction();
        $buzzRequest = new Request();
        $buzzRequest->setFields($instruction->toNvp());
        $exception = null;
        try {
            $response = $this->payment->getApi()->doPayment($buzzRequest);
        } catch (HttpException $e) {
            $response = $e->getResponse();
            $exception = $e;
        }

        $instruction->fromNvp($response);

        if ($exception) {
            throw $exception;
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
