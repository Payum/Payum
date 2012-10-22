<?php
namespace Payum\Examples;

use Payum\Action\ActionInterface;
use Payum\Request\RedirectUrlInteractiveRequest;

class AuthorizeAction implements ActionInterface
{
    /**
     * {@inheritdoc}
     */
    public function execute($request)
    {   
        throw new RedirectUrlInteractiveRequest('http://login.thePayment.com');
    }

    /**
     * {@inheritdoc}
     */
    public function supports($request)
    {
        return $request instanceof AuthorizeRequest;
    }
}