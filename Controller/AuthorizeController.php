<?php
namespace Payum\Bundle\PayumBundle\Controller;

use Payum\Core\Request\SecuredAuthorize;
use Symfony\Component\HttpFoundation\Request;

class AuthorizeController extends PayumController
{
    public function doAction(Request $request)
    {
        $token = $this->getHttpRequestVerifier()->verify($request);

        $payment = $this->getPayum()->getPayment($token->getPaymentName());
        $payment->execute(new SecuredAuthorize($token));
        
        $this->getHttpRequestVerifier()->invalidate($token);
        
        return $this->redirect($token->getAfterUrl());
    }
}