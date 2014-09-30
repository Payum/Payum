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
    public function __construct(StorageInterface $tokenStorage, StorageRegistryInterface $storageRegistry, $capturePath, $notifyPath, $authorizePath, $creditPath)
    {
        $this->tokenStorage = $tokenStorage;
        $this->storageRegistry = $storageRegistry;

        $this->capturePath = $capturePath;
        $this->notifyPath = $notifyPath;
        $this->authorizePath = $authorizePath;
        $this->creditPath = $creditPath;
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

        $targetParameters = array_replace($targetParameters, array('payum_token' => $token->getHash()));
        if (0 === strpos($targetPath, 'http')) {
            if (false !== strpos($targetPath, '?')) {
                $targetPath .= '&'.http_build_query($targetParameters);
            } else {
                $targetPath .= '?'.http_build_query($targetParameters);
            }

            $token->setTargetUrl($targetPath);
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
    public function createCreditToken($paymentName, $model, $afterPath, array $afterParameters = array())
    {
        $afterToken = $this->createToken($paymentName, $model, $afterPath, $afterParameters);

        $creditToken = $this->createToken($paymentName, $model, $this->creditPath);
        $creditToken->setAfterUrl($afterToken->getTargetUrl());

        $this->tokenStorage->updateModel($creditToken);

        return $creditToken;
    }

    /**
     * {@inheritDoc}
     */
    public function createNotifyToken($paymentName, $model = null)
    {
        return $this->createToken($paymentName, $model, $this->notifyPath);
    }

    /**
     * @param string $path
     * @param array $parameters
     *
     * @return string
     */
    abstract protected function generateUrl($path, array $parameters = array());
}
