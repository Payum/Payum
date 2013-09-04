<?php
namespace Payum\Bundle\PayumBundle\Controller;

use Payum\Registry\RegistryInterface;
use Payum\Security\HttpRequestVerifierInterface;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

abstract class PayumController extends Controller
{
    /**
     * @return RegistryInterface
     */
    protected function getPayum()
    {
        return $this->get('payum');
    }

    /**
     * @return HttpRequestVerifierInterface
     */
    protected function getHttpRequestVerifier()
    {
        return $this->get('payum.security.http_request_verifier');
    }
}