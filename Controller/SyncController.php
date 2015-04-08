<?php
namespace Payum\Bundle\PayumBundle\Controller;

use Payum\Core\Request\Sync;
use Symfony\Component\HttpFoundation\Request;

class SyncController extends PayumController
{
    public function doAction(Request $request)
    {
        $token = $this->getHttpRequestVerifier()->verify($request);

        $gateway = $this->getPayum()->getGateway($token->getGatewayName());

        $gateway->execute(new Sync($token));
        
        $this->getHttpRequestVerifier()->invalidate($token);
        
        return $this->redirect($token->getAfterUrl());
    }
}