<?php
namespace Payum\Paypal\ExpressCheckout\Nvp\Action;

use Payum\Action\ActionInterface;
use Payum\Request\RedirectUrlInteractiveRequest;
use Payum\Exception\RequestNotSupportedException;
use Payum\Exception\LogicException;
use Payum\Paypal\ExpressCheckout\Nvp\Request\AuthorizeTokenRequest;

class AuthorizeTokenAction extends ActionPaymentAware
{
    /**
     * {@inheritdoc}
     * 
     * @throws \Payum\Exception\LogicException if the token not set in the instruction.
     * @throws \Payum\Request\RedirectUrlInteractiveRequest if authorization required.
     */
    public function execute($request)
    {
        /** @var $request AuthorizeTokenRequest */
        if (false == $this->supports($request)) {
            throw RequestNotSupportedException::createActionNotSupported($this, $request);
        }
        
        $instruction = $request->getPaymentInstruction();
        if (false == $instruction->getToken()) {
            throw new LogicException('The token must be set. Have you run SetExpressCheckoutAction?');
        }
          
        if (false == $request->getPaymentInstruction()->getPayerid() || $request->isForced()) {
            throw new RedirectUrlInteractiveRequest(
                $this->payment->getApi()->getAuthorizeTokenUrl($instruction->getToken())
            );
        }
    }

    /**
     * {@inheritdoc}
     */
    public function supports($request)
    {
        return $request instanceof AuthorizeTokenRequest;
    }
}