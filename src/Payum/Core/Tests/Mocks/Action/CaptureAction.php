<?php

namespace Payum\Core\Tests\Mocks\Action;

use Payum\Core\Action\ActionInterface;
use Payum\Core\GatewayAwareInterface;
use Payum\Core\GatewayAwareTrait;
use Payum\Core\Request\Capture;
use Payum\Core\Tests\Mocks\Model\AuthorizeRequiredModel;
use Payum\Core\Tests\Mocks\Request\AuthorizeRequest;

class CaptureAction implements ActionInterface, GatewayAwareInterface
{
    use GatewayAwareTrait;

    public function execute($request): void
    {
        /** @var Capture $request */
        if ($request->getModel() instanceof AuthorizeRequiredModel) {
            $this->gateway->execute(new AuthorizeRequest());
        }

        //sell code here.
    }

    public function supports($request)
    {
        return $request instanceof Capture;
    }
}
