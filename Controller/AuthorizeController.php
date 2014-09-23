<?php
namespace Payum\Bundle\PayumBundle\Controller;

use Payum\Core\Request\Authorize;
use Symfony\Component\HttpFoundation\Request;

class AuthorizeController extends PayumController
{
    public function doAction(Request $request)
    {
        $token = $this->getHttpRequestVerifier()->verify($request);

        $payment = $this->getPayum()->getPayment($token->getPaymentName());
        $payment->execute(new Authorize($token));
        
        $this->getHttpRequestVerifier()->invalidate($token);
        
        return $this->redirect($token->getAfterUrl());
    }
}