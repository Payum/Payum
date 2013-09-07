<?php
namespace Payum\Bundle\PayumBundle\Controller;

use Payum\Request\SecuredNotifyRequest;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class NotifyController extends PayumController
{
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

    /**
     * @deprecated since 0.6 will be removed in 0.7. This route present for easy migration from 0.5 version.
     */
    public function doDeprecatedAction(Request $request)
    {
        return $this->forward('Payum:Notify:do', array(
            'payum_token' => $request->attributes->get('payumToken', $request->get('payumToken'))
        ));
    }
}