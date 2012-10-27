<?php
namespace Payum\Paypal\ExpressCheckout\Nvp\Action;

use Payum\Action\ActionInterface;
use Payum\Request\RedirectUrlInteractiveRequest;
use Payum\Exception\RequestNotSupportedException;
use Payum\Exception\LogicException;
use Payum\Paypal\ExpressCheckout\Nvp\Request\AuthorizeTokenRequest;
use Payum\Paypal\ExpressCheckout\Nvp\Api;

class AuthorizeTokenAction implements ActionInterface
{
    /**
     * @var \Payum\Paypal\ExpressCheckout\Nvp\Api
     */
    protected $api;

    /**
     * @param \Payum\Paypal\ExpressCheckout\Nvp\Api $api
     */
    public function __construct(Api $api)
    {
        $this->api = $api;
    }

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
        
        $instruction = $request->getInstruction();
        if (false == $instruction->getToken()) {
            throw new LogicException('The token must be set. Have you run SetExpressCheckoutAction?');
        }
          
        if (false == $request->getInstruction()->getPayerid() || $request->isForced()) {
            throw new RedirectUrlInteractiveRequest($this->api->getAuthorizeTokenUrl($instruction->getToken()));
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