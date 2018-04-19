<?php
namespace Payum\Core\Tests\Mocks\Action;

use Payum\Core\Action\ActionInterface;
use Payum\Core\Reply\HttpRedirect;
use Payum\Core\Tests\Mocks\Request\AuthorizeRequest;

class AuthorizeAction implements ActionInterface
{
    /**
     * {@inheritDoc}
     */
    public function execute($request)
    {
        throw new HttpRedirect('http://login.thePayment.com');
    }

    /**
     * {@inheritDoc}
     */
    public function supports($request)
    {
        return $request instanceof AuthorizeRequest;
    }
}
