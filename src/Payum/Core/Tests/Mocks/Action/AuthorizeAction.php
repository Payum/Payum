<?php
namespace Payum\Core\Tests\Mocks\Action;

use Payum\Core\Action\ActionInterface;
use Payum\Core\Request\RedirectUrlInteractiveRequest;
use Payum\Core\Tests\Mocks\Request\AuthorizeRequest;

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