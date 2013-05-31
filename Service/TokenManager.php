<?php
namespace Payum\Bundle\PayumBundle\Service;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\RouterInterface;

use Payum\Registry\RegistryInterface;
use Payum\Exception\LogicException;
use Payum\Model\TokenizedDetails;
use Payum\Storage\StorageInterface;

class TokenManager 
{
    /**
     * @var \Symfony\Component\Routing\RouterInterface
     */
    protected $router;

    /**
     * @var RegistryInterface
     */
    protected $payum;

    /**
     * @param RouterInterface $router
     * @param RegistryInterface $payum
     */
    public function __construct(RouterInterface $router, RegistryInterface $payum)
    {
        $this->router = $router;
        $this->payum = $payum;
    }

    /**
     * @param Request $request
     *
     * @throws \Symfony\Component\HttpKernel\Exception\HttpException
     * 
     * @return \Payum\Model\TokenizedDetails
     */
    public function getTokenFromRequest(Request $request)
    {
        if (false === $paymentName = $request->attributes->get('paymentName', $request->get('paymentName', false))) {
            throw new HttpException(404, 'Payment name not set in request');
        }
        if (false === $token = $request->attributes->get('token', $request->get('token', false))) {
            throw new HttpException(404, 'Token not set in request');
        }

        $isSubRequest = true;
        if (false == $token instanceof TokenizedDetails) {
            $isSubRequest = false;
            if (false == $token = $this->findByToken($paymentName, $token)) {
                throw new NotFoundHttpException('The TokenizedDetails with requested token not found.');
            }
        }

        /** @var $token TokenizedDetails */

        if ($paymentName !== $token->getPaymentName()) {
            throw new HttpException(400, sprintf('The paymentName %s not match one %s set in the token.', $paymentName, $token->getPaymentName()));
        }
        
        if (false === $isSubRequest && parse_url($request->getUri(), PHP_URL_PATH) != parse_url($token->getTargetUrl(), PHP_URL_PATH)) {
            throw new HttpException(400, sprintf('The current url %s not match target url %s set in the token.', $request->getRequestUri(), $token->getTargetUrl()));
        }
        
        return $token;
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
     * @param string $afterRoute
     * @param array $afterRouteParameters
     * 
     * @return TokenizedDetails
     */
    public function createTokenForRoute($paymentName, $model, $targetRoute, array $targetRouteParameters = array(), $afterRoute = null, array $afterRouteParameters = array())
    {
        $tokenStorage = $this->getStorage($paymentName);
        $modelDetailsStorage = $this->payum->getStorageForClass($model, $paymentName);

        /** @var TokenizedDetails $tokenizedDetails */
        $tokenizedDetails = $tokenStorage->createModel();
        $tokenizedDetails->setDetails($modelDetailsStorage->getIdentificator($model));
        $tokenizedDetails->setPaymentName($paymentName);
        $tokenizedDetails->setTargetUrl($this->router->generate($targetRoute, array_replace($targetRouteParameters, array(
            'paymentName' => $paymentName,
            'token' => $tokenizedDetails->getToken()
        )), $absolute = true));

        if ($afterRoute) {
            $tokenizedDetails->setAfterUrl(
                $this->router->generate($afterRoute, $afterRouteParameters, $absolute = true)
            );
        }

        $tokenStorage->updateModel($tokenizedDetails);

        return $tokenizedDetails;
    }

    /**
     * @param string $paymentName
     * @param string $token
     *
     * @return TokenizedDetails
     */
    public function findByToken($paymentName, $token)
    {
        $storage = $this->getStorage($paymentName);
        
        return $storage->findModelById($token);
    }

    /**
     * @param TokenizedDetails $token
     */
    public function deleteToken(TokenizedDetails $token)
    {
        $this->payum->getStorageForClass($token, $token->getPaymentName())->deleteModel($token);
    }

    /**
     * @param string $paymentName
     *
     * @throws LogicException when storage for TokenizedDetails instance not found
     *
     * @return StorageInterface
     */
    public function getStorage($paymentName)
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