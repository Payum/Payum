<?php
namespace Payum\Examples\Action;

use Payum\Action\ActionInterface;
use Payum\Request\RedirectUrlInteractiveRequest;
use Payum\Examples\Request\AuthorizeRequest;

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