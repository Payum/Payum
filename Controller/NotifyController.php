<?php
namespace Payum\Bundle\PayumBundle\Controller;

use Payum\Core\Request\NotifyRequest;
use Payum\Core\Request\SecuredNotifyRequest;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class NotifyController extends PayumController
{
    public function doUnsafeAction(Request $request)
    {
        $payment = $this->getPayum()->getPayment($request->get('payment_name'));

        $payment->execute(new NotifyRequest(array_replace(
            $request->query->all(),
            $request->request->all()
        )));

        return new Response('', 204);
    }

    public function doAction(Request $request)
    {
        $token = $this->getHttpRequestVerifier()->verify($request);

        $payment = $this->getPayum()->getPayment($token->getPaymentName());

        $payment->execute(new SecuredNotifyRequest(
            array_replace($request->query->all(), $request->request->all()),
            $token
        ));

        return new Response('', 204);
    }
}