<?php
namespace Payum\Bundle\PayumBundle\Controller;

use Payum\Core\Request\Notify;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class NotifyController extends PayumController
{
    public function doUnsafeAction(Request $request)
    {
        $gateway = $this->getPayum()->getGateway($request->get('gateway'));

        $gateway->execute(new Notify(null));

        return new Response('', 204);
    }

    public function doAction(Request $request)
    {
        $token = $this->getHttpRequestVerifier()->verify($request);

        $gateway = $this->getPayum()->getGateway($token->getGatewayName());

        $gateway->execute(new Notify($token));

        return new Response('', 204);
    }
}