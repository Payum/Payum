<?php
namespace Payum\Bundle\PayumBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;

use Payum\Exception\RequestNotSupportedException;
use Payum\Request\BinaryMaskStatusRequest;
use Payum\Registry\RegistryInterface;
use Payum\Bundle\PayumBundle\Request\CaptureTokenizedDetailsRequest;
use Payum\Bundle\PayumBundle\Service\TokenManager;

class CaptureController extends Controller 
{
    public function doAction(Request $request)
    {
        $token = $this->getTokenManager()->getTokenFromRequest($request);

        $payment = $this->getPayum()->getPayment($token->getPaymentName());
        
        $status = new BinaryMaskStatusRequest($token);
        $payment->execute($status);
        if (false == $status->isNew()) {
            throw new HttpException(400, 'The model status must be new.');
        }
        
        $capture = new CaptureTokenizedDetailsRequest($token);
        $payment->execute($capture);
        
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