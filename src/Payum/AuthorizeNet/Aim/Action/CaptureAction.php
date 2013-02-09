<?php
namespace Payum\AuthorizeNet\Aim\Action;

use Payum\Request\CaptureRequest;
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

        /** @var $instruction PaymentInstruction */
        $instruction = $request->getModel();
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