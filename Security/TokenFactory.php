<?php
namespace Payum\Bundle\PayumBundle\Security;

use Payum\Registry\RegistryInterface;
use Payum\Security\TokenInterface;
use Payum\Storage\StorageInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\RouterInterface;

class TokenFactory
{
    /**
     * @var \Symfony\Component\Routing\RouterInterface
     */
    protected $router;

    /**
     * @var \Payum\Storage\StorageInterface
     */
    protected $tokenStorage;

    /**
     * @var \Payum\Registry\RegistryInterface
     */
    protected $payum;

    /**
     * @param RouterInterface $router
     * @param \Payum\Storage\StorageInterface $tokenStorage
     * @param \Payum\Registry\RegistryInterface $payum
     */
    public function __construct(RouterInterface $router, StorageInterface $tokenStorage, RegistryInterface $payum)
    {
        $this->router = $router;
        $this->tokenStorage = $tokenStorage;
        $this->payum = $payum;
    }

    /**
     * @param string $paymentName
     * @param object $model
     * @param string $afterRoute
     * @param array $afterRouteParameters
     * 
     * @return TokenInterface
     */
    public function createCaptureToken($paymentName, $model, $afterRoute, array $afterRouteParameters = array())
    {
        $afterToken = $this->createTokenForRoute($paymentName, $model, $afterRoute, $afterRouteParameters);
        
        $captureToken = $this->createTokenForRoute( $paymentName, $model, 'payum_capture_do');
        $captureToken->setAfterUrl($afterToken->getTargetUrl());
        
        $this->tokenStorage->updateModel($captureToken);
        
        return $captureToken;
    }

    /**
     * @param string $paymentName
     * @param object $model
     *
     * @return TokenInterface
     */
    public function createNotifyToken($paymentName, $model)
    {
        return $this->createTokenForRoute($paymentName, $model, 'payum_notify_do');
    }

    /**
     * @param string $paymentName
     * @param object $model
     * @param string $targetRoute
     * @param array $targetRouteParameters
     * @param string $afterRoute
     * @param array $afterRouteParameters
     * 
     * @return TokenInterface
     */
    public function createTokenForRoute($paymentName, $model, $targetRoute, array $targetRouteParameters = array(), $afterRoute = null, array $afterRouteParameters = array())
    {
        $modelStorage = $this->payum->getStorageForClass($model, $paymentName);

        /** @var TokenInterface $token */
        $token = $this->tokenStorage->createModel();
        $token->setDetails($modelStorage->getIdentificator($model));
        $token->setPaymentName($paymentName);
        $token->setTargetUrl($this->router->generate($targetRoute, array_replace($targetRouteParameters, array(
            'payum_token' => $token->getHash()
        )), $absolute = true));

        if ($afterRoute) {
            $token->setAfterUrl(
                $this->router->generate($afterRoute, $afterRouteParameters, $absolute = true)
            );
        }

        $this->tokenStorage->updateModel($token);

        return $token;
    }
}