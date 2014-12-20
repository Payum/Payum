<?php
namespace Payum\Core\Security;

use League\Url\Url;
use Payum\Core\Registry\StorageRegistryInterface;
use Payum\Core\Storage\IdentityInterface;
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
    protected $refundPath;

    /**
     * @var string
     */
    protected $notifyPath;

    /**
     * @var string
     */
    protected $authorizePath;

    /**
     * @param StorageInterface         $tokenStorage
     * @param StorageRegistryInterface $storageRegistry
     * @param string                   $capturePath
     * @param string                   $notifyPath
     * @param string                   $authorizePath
     * @param string                   $refundPath
     */
    public function __construct(StorageInterface $tokenStorage, StorageRegistryInterface $storageRegistry, $capturePath, $notifyPath, $authorizePath, $refundPath)
    {
        $this->tokenStorage = $tokenStorage;
        $this->storageRegistry = $storageRegistry;

        $this->capturePath = $capturePath;
        $this->refundPath = $refundPath;
        $this->notifyPath = $notifyPath;
        $this->authorizePath = $authorizePath;
    }

    /**
     * {@inheritDoc}
     */
    public function createToken($paymentName, $model, $targetPath, array $targetParameters = array(), $afterPath = null, array $afterParameters = array())
    {
        /** @var TokenInterface $token */
        $token = $this->tokenStorage->create();

        $token->setPaymentName($paymentName);

        if ($model instanceof IdentityInterface) {
            $token->setDetails($model);
        } elseif (null !== $model) {
            $token->setDetails($this->storageRegistry->getStorage($model)->identify($model));
        }

        if (0 === strpos($targetPath, 'http')) {
            $targetUrl = Url::createFromUrl($targetPath);
            $targetUrl->getQuery()->set(array_replace(
                array('payum_token' => $token->getHash()),
                $targetUrl->getQuery()->toArray(),
                $targetParameters
            ));

            $token->setTargetUrl((string) $targetUrl);
        } else {
            $token->setTargetUrl($this->generateUrl($targetPath, array_replace(
                array('payum_token' => $token->getHash()),
                $targetParameters
            )));
        }

        if ($afterPath && 0 === strpos($afterPath, 'http')) {
            $afterUrl = Url::createFromUrl($afterPath);
            $afterUrl->getQuery()->modify($afterParameters);

            $token->setAfterUrl((string) $afterUrl);
        } elseif ($afterPath) {
            $token->setAfterUrl($this->generateUrl($afterPath, $afterParameters));
        }

        $this->tokenStorage->update($token);

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

        $this->tokenStorage->update($captureToken);

        return $captureToken;
    }

    /**
     * {@inheritDoc}
     */
    public function createRefundToken($paymentName, $model, $afterPath = null, array $afterParameters = array())
    {
        $refundToken = $this->createToken($paymentName, $model, $this->refundPath);

        if ($afterPath) {
            $afterToken = $this->createToken($paymentName, $model, $afterPath, $afterParameters);
            $refundToken->setAfterUrl($afterToken->getTargetUrl());
        }

        $this->tokenStorage->update($refundToken);

        return $refundToken;
    }

    /**
     * {@inheritDoc}
     */
    public function createAuthorizeToken($paymentName, $model, $afterPath, array $afterParameters = array())
    {
        $afterToken = $this->createToken($paymentName, $model, $afterPath, $afterParameters);

        $authorizeToken = $this->createToken($paymentName, $model, $this->authorizePath);
        $authorizeToken->setAfterUrl($afterToken->getTargetUrl());

        $this->tokenStorage->update($authorizeToken);

        return $authorizeToken;
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
     * @param array  $parameters
     *
     * @return string
     */
    abstract protected function generateUrl($path, array $parameters = array());
}
