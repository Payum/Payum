<?php
namespace Payum\Paypal\ExpressCheckout\Nvp\Action\Api;

use Payum\Bridge\Spl\ArrayObject;
use Payum\Paypal\ExpressCheckout\Nvp\Action\Api\BaseApiAwareAction;
use Payum\Request\RedirectUrlInteractiveRequest;
use Payum\Exception\RequestNotSupportedException;
use Payum\Exception\LogicException;
use Payum\Paypal\ExpressCheckout\Nvp\Request\Api\AuthorizeTokenRequest;

class AuthorizeTokenAction extends BaseApiAwareAction
{
    /**
     * {@inheritdoc}
     * 
     * @throws \Payum\Exception\LogicException if the token not set in the instruction.
     * @throws \Payum\Request\RedirectUrlInteractiveRequest if authorization required.
     */
    public function execute($request)
    {
        /** @var $request \Payum\Paypal\ExpressCheckout\Nvp\Request\Api\AuthorizeTokenRequest */
        if (false == $this->supports($request)) {
            throw RequestNotSupportedException::createActionNotSupported($this, $request);
        }
        
        $model = ArrayObject::ensureArrayObject($request->getModel());
        if (false == $model['TOKEN']) {
            throw new LogicException('The TOKEN must be set. Have you executed SetExpressCheckoutAction?');
        }
          
        if (false == $model['PAYERID'] || $request->isForced()) {
            throw new RedirectUrlInteractiveRequest(
                $this->api->getAuthorizeTokenUrl($model['TOKEN'])
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