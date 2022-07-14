<?php

namespace Payum\Core\Tests\Mocks\Action;

use Payum\Core\Action\ActionInterface;
use Payum\Core\Reply\HttpRedirect;
use Payum\Core\Tests\Mocks\Request\AuthorizeRequest;

class AuthorizeAction implements ActionInterface
{
    public function execute(mixed $request): void
    {
        throw new HttpRedirect('http://login.thePayment.com');
    }

    public function supports(mixed $request): bool
    {
        return $request instanceof AuthorizeRequest;
    }
}
