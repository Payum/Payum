<?php
namespace Payum\Be2Bill\Action;

use Payum\Core\Action\GatewayAwareAction;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Reply\HttpResponse;
use Payum\Core\Request\GetHttpRequest;
use Payum\Core\Request\GetToken;
use Payum\Core\Request\Notify;

class NotifyNullAction extends GatewayAwareAction
{
    /**
     * {@inheritDoc}
     *
     * @param $request Notify
     */
    public function execute($request)
    {
        RequestNotSupportedException::assertSupports($this, $request);

        $this->gateway->execute($httpRequest = new GetHttpRequest());

        //we are back from be2bill site so we have to just update model.
        if (empty($httpRequest->query['EXTRADATA'])) {
            throw new HttpResponse('The notification is invalid. Code Be2Bell1', 400);
        }

        $extraDataJson = $httpRequest->query['EXTRADATA'];
        if (false == $extraData = json_decode($extraDataJson, true)) {
            throw new HttpResponse('The notification is invalid. Code Be2Bell2', 400);
        }

        if (empty($extraData['notify_token'])) {
            throw new HttpResponse('The notification is invalid. Code Be2Bell3', 400);
        }

        $this->gateway->execute($getToken = new GetToken($extraData['notify_token']));
        $this->gateway->execute(new Notify($getToken->getToken()));
    }

    /**
     * {@inheritDoc}
     */
    public function supports($request)
    {
        return
            $request instanceof Notify &&
            null === $request->getModel()
        ;
    }
}
