<?php
namespace Payum\Core\Tests\Mocks\Action;

use Payum\Core\Action\ActionInterface;
use Payum\Core\GatewayAwareInterface;
use Payum\Core\GatewayAwareTrait;
use Payum\Core\Tests\Mocks\Model\AuthorizeRequiredModel;
use Payum\Core\Request\Capture;
use Payum\Core\Tests\Mocks\Request\AuthorizeRequest;

class CaptureAction implements ActionInterface, GatewayAwareInterface
{
    use GatewayAwareTrait;

    /**
     * {@inheritDoc}
     */
    public function execute($request)
    {
        /** @var $request Capture */
        if ($request->getModel() instanceof AuthorizeRequiredModel) {
            $this->gateway->execute(new AuthorizeRequest());
        }

        //sell code here.
    }

    /**
     * {@inheritDoc}
     */
    public function supports($request)
    {
        return $request instanceof Capture;
    }
}
