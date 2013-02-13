<?php
namespace Payum\Paypal\ExpressCheckout\Nvp\Action;

use Buzz\Message\Form\FormRequest;

use Payum\PaymentInstructionAggregateInterface;
use Payum\Paypal\ExpressCheckout\Nvp\PaymentInstruction;
use Payum\Request\SyncRequest;
use Payum\Action\ActionPaymentAware as BaseActionPaymentAware;
use Payum\Exception\RequestNotSupportedException;
use Payum\Paypal\ExpressCheckout\Nvp\Exception\Http\HttpResponseAckNotSuccessException;
use Payum\Paypal\ExpressCheckout\Nvp\Request\GetExpressCheckoutDetailsRequest;
use Payum\Paypal\ExpressCheckout\Nvp\Request\GetTransactionDetailsRequest;

class SyncAction extends BaseActionPaymentAware
{
    /**
     * {@inheritdoc}
     */
    public function execute($request)
    {
        if (false == $this->supports($request)) {
            throw RequestNotSupportedException::createActionNotSupported($this, $request);
        }
        
        $instruction = $this->getPaymentInstructionFromRequest($request);
        
        if (false == $instruction->getToken()) {
            return;
        }
        
        try {
            $this->payment->execute(new GetExpressCheckoutDetailsRequest($instruction));
            
            foreach ($instruction->getPaymentrequestTransactionid() as $paymentRequestN => $transactionId) {
                $this->payment->execute(new GetTransactionDetailsRequest($paymentRequestN, $instruction));
            }
        } catch (HttpResponseAckNotSuccessException $e) {
            $instruction->clearErrors();
            $instruction->fromNvp($e->getResponse());
        }
    }

    /**
     * {@inheritdoc}
     */
    public function supports($request)
    {
        if (false == $request instanceof SyncRequest) {
            return false;
        }
        
        return (bool) $this->getPaymentInstructionFromRequest($request);
    }

    /**
     * @param SyncRequest $request
     *
     * @return PaymentInstruction|null
     */
    protected function getPaymentInstructionFromRequest(SyncRequest $request)
    {
        if ($request->getModel() instanceof PaymentInstruction) {
            return $request->getModel();
        }

        if (
            $request->getModel() instanceof PaymentInstructionAggregateInterface &&
            $request->getModel()->getPaymentInstruction() instanceof PaymentInstruction
        ) {
            return $request->getModel()->getPaymentInstruction();
        }
    }
}