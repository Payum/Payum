<?php
namespace Payum\Paypal\AdaptivePayments\Json\Action;

use Payum\Core\Action\ActionInterface;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\GatewayAwareInterface;
use Payum\Core\GatewayAwareTrait;
use Payum\Core\Request\Capture;
use Payum\Core\Request\Sync;
use Payum\Paypal\AdaptivePayments\Json\Api;
use Payum\Paypal\AdaptivePayments\Json\Request\Api\AuthorizeKey;
use Payum\Paypal\AdaptivePayments\Json\Request\Api\Pay;

class CaptureAction implements ActionInterface, GatewayAwareInterface
{
    use GatewayAwareTrait;

    /**
     * {@inheritdoc}
     */
    public function execute($request)
    {
        /** @var $request Capture */
        RequestNotSupportedException::assertSupports($this, $request);

        $details = ArrayObject::ensureArrayObject($request->getModel());

        if (false == $details['payKey']) {
            if (false == $details['returnUrl'] && $request->getToken()) {
                $details['returnUrl'] = $request->getToken()->getTargetUrl();
            }

            if (false == $details['cancelUrl'] && $request->getToken()) {
                $details['cancelUrl'] = $request->getToken()->getTargetUrl();
            }

            $this->gateway->execute(new Pay($details));

            $responseEnvelope = $details->getArray('responseEnvelope');

            if (false == in_array($responseEnvelope['ack'], [Api::ACK_SUCCESS, Api::ACK_SUCCESS_WITH_WARNING], true)) {
                return;
            }
        }

        $this->gateway->execute(new Sync($details));

        if ($details['payKey'] && $details['status'] === Api::PAYMENT_STATUS_CREATED) {
            $this->gateway->execute(new AuthorizeKey($details));
        }
    }

    /**
     * {@inheritdoc}
     */
    public function supports($request)
    {
        return
            $request instanceof Capture &&
            $request->getModel() instanceof \ArrayAccess
        ;
    }
}
