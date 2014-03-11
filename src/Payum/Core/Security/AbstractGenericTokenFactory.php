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
     * @param StorageInterface $tokenStorage
     * @param StorageRegistryInterface $payum
     * @param string $capturePath
     * @param string $notifyPath
     */
    public function __construct(StorageInterface $tokenStorage, StorageRegistryInterface $storageRegistry, $capturePath, $notifyPath)
    {
        $this->tokenStorage = $tokenStorage;
        $this->storageRegistry = $storageRegistry;

        $this->capturePath = $capturePath;
        $this->notifyPath = $notifyPath;
    }

    /**
     * {@inheritDoc}
     */
    public function createToken($paymentName, $model, $targetPath, array $targetParameters = array(), $afterPath = null, array $afterParameters = array())
    {
        $modelStorage = $this->storageRegistry->getStorageForClass($model, $paymentName);

        /** @var TokenInterface $token */
        $token = $this->tokenStorage->createModel();
        $token->setDetails($modelStorage->getIdentificator($model));
        $token->setPaymentName($paymentName);
        $token->setTargetUrl($this->generateUrl($targetPath, array_replace($targetParameters, array(
            'payum_token' => $token->getHash()
        ))));

        if ($afterPath) {
            $token->setAfterUrl($this->generateUrl($afterPath, $afterParameters));
        }

        $this->tokenStorage->updateModel($token);

        return $token;
    }

    /**
     * @param string $paymentName
     * @param object $model
     * @param string $afterPath
     * @param array $afterParameters
     *
     * @return TokenInterface
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
     * @param string $paymentName
     * @param object $model
     *
     * @return TokenInterface
     */
    public function createNotifyToken($paymentName, $model)
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
