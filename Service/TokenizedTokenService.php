<?php
namespace Payum\Bundle\PayumBundle\Service;

use Symfony\Component\Routing\RouterInterface;

use Payum\Bundle\PayumBundle\Registry\ContainerAwareRegistry;
use Payum\Exception\LogicException;
use Payum\Model\TokenizedDetails;
use Payum\Storage\StorageInterface;

class TokenizedTokenService 
{
    /**
     * @var \Symfony\Component\Routing\RouterInterface
     */
    protected $router;

    /**
     * @var \Payum\Bundle\PayumBundle\Registry\ContainerAwareRegistry
     */
    protected $payum;

    /**
     * @param RouterInterface $router
     * @param ContainerAwareRegistry $payum
     */
    public function __construct(RouterInterface$router, ContainerAwareRegistry $payum)
    {
        $this->router = $router;
        $this->payum = $payum;
    }

    /**
     * @param string $paymentName
     * @param object $model
     * @param string $afterRoute
     * @param array $afterRouteParameters
     * 
     * @return TokenizedDetails
     */
    public function createTokenForCaptureRoute($paymentName, $model, $afterRoute, array $afterRouteParameters = array())
    {
        $afterToken = $this->createTokenForRoute(
            $paymentName,
            $model,
            $afterRoute,
            $afterRouteParameters
        );
        
        $captureToken = $this->createTokenForRoute(
            $paymentName, 
            $model, 
            'payum_capture_do'
        );
        $captureToken->setAfterUrl($afterToken->getTargetUrl());
        
        $this->payum->getStorageForClass($captureToken, $paymentName)->updateModel($captureToken);
        
        return $captureToken;
    }

    /**
     * @param string $paymentName
     * @param object $model
     * @param string $targetRoute
     * @param array $targetRouteParameters
     * 
     * @return TokenizedDetails
     */
    public function createTokenForRoute($paymentName, $model, $targetRoute, array $targetRouteParameters = array())
    {
        $tokenizedDetailsStorage = $this->findTokenizedDetailsStorage($paymentName);
        $modelDetailsStorage = $this->payum->getStorageForClass($model, $paymentName);

        /** @var TokenizedDetails $tokenizedDetails */
        $tokenizedDetails = $tokenizedDetailsStorage->createModel();
        $tokenizedDetails->setDetails($modelDetailsStorage->getIdentificator($model));
        $tokenizedDetails->setPaymentName($paymentName);
        $tokenizedDetails->setTargetUrl($this->router->generate($targetRoute, array_replace($targetRouteParameters, array(
            'paymentName' => $paymentName,
            'token' => $tokenizedDetails->getToken()
        )), $absolute = true));

        $tokenizedDetailsStorage->updateModel($tokenizedDetails);

        return $tokenizedDetails;
    }

    /**
     * @param string $paymentName
     * @param string $token
     *
     * @return TokenizedDetails
     */
    public function findTokenizedDetailsByToken($paymentName, $token)
    {
        $storage = $this->findTokenizedDetailsStorage($paymentName);
        
        return $storage->findModelById($token);
    }

    /**
     * @param string $paymentName
     *
     * @throws LogicException when storage for TokenizedDetails instance not found
     *
     * @return StorageInterface
     */
    public function findTokenizedDetailsStorage($paymentName)
    {
        foreach ($this->payum->getStorages($paymentName) as $modelClass => $storage) {
            if (is_subclass_of($modelClass, 'Payum\Model\TokenizedDetails')) {
                return $storage;
            }
        }

        throw new LogicException(sprintf(
            'Cannot find storage that supports %s for payment %s',
            'Payum\Model\TokenizedDetails',
            $paymentName
        ));
    }
}