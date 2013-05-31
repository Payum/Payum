<?php
namespace Payum\Bundle\PayumBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

use Payum\Request\SyncRequest;
use Payum\Registry\RegistryInterface;
use Payum\Exception\RequestNotSupportedException;
use Payum\Bundle\PayumBundle\Service\TokenManager;

class SyncController extends Controller 
{
    public function doAction(Request $request)
    {
        $token = $this->getTokenManager()->getTokenFromRequest($request);

        $payment = $this->getPayum()->getPayment($token->getPaymentName());
        
        $sync = new SyncRequest($token);
        $payment->execute($sync);
        
        $this->getTokenManager()->deleteToken($token);
        
        return $this->redirect($token->getAfterUrl());
    }

    /**
     * @return RegistryInterface
     */
    protected function getPayum()
    {
        return $this->get('payum');
    }

    /**
     * @return TokenManager
     */
    protected function getTokenManager()
    {
        return $this->get('payum.token_manager');
    }
}