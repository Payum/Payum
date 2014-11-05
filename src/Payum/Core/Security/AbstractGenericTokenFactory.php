<?php
namespace Payum\Core\Security;

use Payum\Core\Registry\StorageRegistryInterface;
use Payum\Core\Storage\StorageInterface;

abstract class AbstractGenericTokenFactory implements GenericTokenFactoryInterface
{
    /**
     * @var StorageInterface
     */
    protected $tokenStorage;

    /**
     * @var StorageRegistryInterface
     */
    protected $storageRegistry;

    /**
     * @var string
     */
    protected $capturePath;

    /**
     * @var string
     */
    protected $notifyPath;

    /**
     * @var string
     */
    protected $authorizePath;

    /**
     * @param StorageInterface $tokenStorage
     * @param StorageRegistryInterface $storageRegistry
     * @param string $capturePath
     * @param string $notifyPath
     * @param string $authorizePath
     */
    public function __construct(StorageInterface $tokenStorage, StorageRegistryInterface $storageRegistry, $capturePath, $notifyPath, $authorizePath)
    {
        $this->tokenStorage = $tokenStorage;
        $this->storageRegistry = $storageRegistry;

        $this->capturePath = $capturePath;
        $this->notifyPath = $notifyPath;
        $this->authorizePath = $authorizePath;
    }

    /**
     * {@inheritDoc}
     */
    public function createToken($paymentName, $model, $targetPath, array $targetParameters = array(), $afterPath = null, array $afterParameters = array())
    {
        /** @var TokenInterface $token */
        $token = $this->tokenStorage->createModel();

        $token->setPaymentName($paymentName);

        if (null !== $model) {
            $token->setDetails(
                $this->storageRegistry->getStorage($model)->getIdentificator($model)
            );
        }

        $targetParametersInPath = array();
        if (0 === strpos($targetPath, 'http') && false !== strpos($targetPath, '?')) {
            list($targetPath, $targetParametersInPath) = explode('?', $targetPath);

            parse_str($targetParametersInPath, $targetParametersInPath);
        }

        $targetParameters = array_replace(array('payum_token' => $token->getHash()), $targetParametersInPath, $targetParameters);
        if (0 === strpos($targetPath, 'http')) {
            $token->setTargetUrl($targetPath.'?'.http_build_query($targetParameters));
        } else {
            $token->setTargetUrl($this->generateUrl($targetPath, $targetParameters));
        }

        if ($afterPath && 0 === strpos($afterPath, 'http')) {
            if (false !== strpos($afterPath, '?')) {
                $afterPath .= '&'.http_build_query($afterParameters);
            } else {
                $afterPath .= '?'.http_build_query($afterParameters);
            }

            $token->setAfterUrl($afterPath);
        } elseif ($afterPath) {
            $token->setAfterUrl($this->generateUrl($afterPath, $afterParameters));
        }

        $this->tokenStorage->updateModel($token);

        return $token;
    }

    /**
     * {@inheritDoc}
     */
    public function createCaptureToken($paymentName, $model, $afterPath, array $afterParameters = array())
    {
        $afterToken = $this->createToken($paymentName, $model, $afterPath, $afterParameters);

        $captureToken = $this->createToken($paymentName, $model, $this->capturePath);
        $captureToken->setAfterUrl($afterToken->getTargetUrl());

        $this->tokenStorage->updateModel($captureToken);

        return $captureToken;
    }

    /**
     * {@inheritDoc}
     */
    public function createAuthorizeToken($paymentName, $model, $afterPath, array $afterParameters = array())
    {
        $afterToken = $this->createToken($paymentName, $model, $afterPath, $afterParameters);

        $authorizeToken = $this->createToken($paymentName, $model, $this->authorizePath);
        $authorizeToken->setAfterUrl($afterToken->getTargetUrl());

        $this->tokenStorage->updateModel($authorizeToken);

        return $authorizeToken;
    }

    /**
     * {@inheritDoc}
     */
    public function createNotifyToken($paymentName, $model = null)
    {
        return $this->createToken($paymentName, $model, $this->notifyPath);
    }

    protected function prepareUrl(TokenInterface $token, $path, array $pathParameters = array())
    {
        $targetParametersInPath = array();
        if (0 === strpos($path, 'http') && false !== strpos($path, '?')) {
            list($path, $targetParametersInPath) = explode('?', $path);

            parse_str($targetParametersInPath, $targetParametersInPath);
        }

        $pathParameters = array_replace(array('payum_token' => $token->getHash()), $targetParametersInPath, $pathParameters);

        return 0 === strpos($path, 'http') ?
            $path.'?'.http_build_query($pathParameters) :
            $this->generateUrl($path, $pathParameters)
        ;
    }

    /**
     * @param string $path
     * @param array $parameters
     *
     * @return string
     */
    abstract protected function generateUrl($path, array $parameters = array());
}