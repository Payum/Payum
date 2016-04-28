<?php
namespace Payum\Paypal\ProHosted\Action;

use Payum\Core\Action\ActionInterface;
use Payum\Core\Action\GatewayAwareAction;
use Payum\Core\ApiAwareInterface;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\LogicException;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Exception\UnsupportedApiException;
use Payum\Core\GatewayAwareInterface;
use Payum\Core\Reply\HttpRedirect;
use Payum\Core\Reply\HttpResponse;
use Payum\Core\Request\Capture;
use Payum\Core\Request\GetHttpRequest;
use Payum\Core\Request\Sync;
use Payum\Paypal\ProHosted\Request\Api\CreateButtonPayment;

class CaptureAction extends GatewayAwareAction
{
    /**
     * {@inheritDoc}
     *
     * @param Capture $request
     */
    public function execute($request)
    {
        /** @var $request Capture */
        RequestNotSupportedException::assertSupports($this, $request);
        $model = ArrayObject::ensureArrayObject($request->getModel());

        $this->gateway->execute($httpRequest = new GetHttpRequest());
        $details = ArrayObject::ensureArrayObject($httpRequest->request);

        if ($details != null && $details['txn_id'] != null) {
            $this->gateway->execute(new Sync($details));

            throw new HttpResponse('OK', 200);
        }

        $this->gateway->execute(new CreateButtonPayment($model));

        if ($model['EMAILLINK'] != null) {
            throw new HttpRedirect($model['EMAILLINK']);
        } else {
            throw new LogicException('Error');
        }
    }

    /**
     * {@inheritDoc}
     */
    public function supports($request)
    {
        return
            $request instanceof Capture &&
            $request->getModel() instanceof \ArrayAccess;
    }
}
