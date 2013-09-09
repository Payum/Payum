<?php
namespace Payum\Bundle\PayumBundle\Controller;

use Payum\Request\SyncRequest;
use Payum\Exception\RequestNotSupportedException;
use Symfony\Component\HttpFoundation\Request;

class SyncController extends PayumController
{
    public function doAction(Request $request)
    {
        $token = $this->getHttpRequestVerifier()->verify($request);

        $payment = $this->getPayum()->getPayment($token->getPaymentName());

        $payment->execute(new SyncRequest($token));
        
        $this->getHttpRequestVerifier()->invalidate($token);
        
        return $this->redirect($token->getAfterUrl());
    }

    /**
     * @deprecated since 0.6 will be removed in 0.7. This route present for easy migration from 0.5 version.
     */
    public function doDeprecatedAction(Request $request)
    {
        return $this->forward('Payum:Sync:do', array(
            'payum_token' => $request->attributes->get('token', $request->get('token'))
        ));
    }
}