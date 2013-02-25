<?php
namespace Payum\Paypal\ExpressCheckout\Nvp\Action;

use Payum\Action\ActionInterface;
use Payum\Bridge\Spl\ArrayObject;
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
        
        $model = new ArrayObject($request->getModel());
        if (false == $model['TOKEN']) {
            throw new LogicException('The TOKEN must be set. Have you executed SetExpressCheckoutAction?');
        }
          
        if (false == $model['PAYERID'] || $request->isForced()) {
            throw new RedirectUrlInteractiveRequest(
                $this->payment->getApi()->getAuthorizeTokenUrl($model['TOKEN'])
            );
        }
    }

    /**
     * {@inheritdoc}
     */
    public function supports($request)
    {
        return 
            $request instanceof AuthorizeTokenRequest &&
            $request->getModel() instanceof \ArrayAccess
        ;
    }
}