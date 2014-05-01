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
     * @param StorageRegistryInterface $storageRegistry
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
        /** @var TokenInterface $token */
        $token = $this->tokenStorage->createModel();

        $token->setPaymentName($paymentName);

        if (null !== $model) {
            $token->setDetails(
                $this->storageRegistry->getStorageForClass($model, $paymentName)->getIdentificator($model)
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
