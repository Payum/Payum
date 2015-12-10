<?php
namespace Payum\Payex\Action;

use Payum\Core\Action\GatewayAwareAction;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Request\Capture;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Request\GetHttpRequest;
use Payum\Payex\Request\Api\StartRecurringPayment;
use Payum\Payex\Request\Api\InitializeOrder;
use Payum\Payex\Request\Api\CompleteOrder;

class PaymentDetailsCaptureAction extends GatewayAwareAction
{
    /**
     * {@inheritDoc}
     *
     * @param Capture $request
     */
    public function execute($request)
    {
        RequestNotSupportedException::assertSupports($this, $request);

        $details = ArrayObject::ensureArrayObject($request->getModel());

        if (false == $details['returnUrl'] && $request->getToken()) {
            $details['returnUrl'] = $request->getToken()->getTargetUrl();
        }

        if (false == $details['cancelUrl'] && $request->getToken()) {
            $details['cancelUrl'] = $request->getToken()->getTargetUrl();
        }

        if (false == $details['clientIPAddress']) {
            $this->gateway->execute($httpRequest = new GetHttpRequest());

            $details['clientIPAddress'] = $httpRequest->clientIp;
        }

        if (false == $details['orderRef']) {
            $this->gateway->execute(new InitializeOrder($details));
        }

        if ($details['orderRef']) {
            $this->gateway->execute(new CompleteOrder($details));

            if ($details['recurring']) {
                $this->gateway->execute(new StartRecurringPayment($details));
            }
        }
    }

    /**
     * {@inheritDoc}
     */
    public function supports($request)
    {
        if (false == (
            $request instanceof Capture &&
            $request->getModel() instanceof \ArrayAccess
        )) {
            return false;
        }

        $model = ArrayObject::ensureArrayObject($request->getModel());

        if ($model['recurring']) {
            return true;
        }

        //Make sure it is not auto pay payment. There is an other capture action for auto pay payments;
        if (false == $model['autoPay']) {
            return true;
        }

        return false;
    }
}
