<?php
namespace Payum\Core\Security;

use League\Url\Url;
use Payum\Core\Model\Identificator;
use Payum\Core\Registry\StorageRegistryInterface;
use Payum\Core\Storage\StorageInterface;
use Payum\Core\Security\Util\Random;
use Payum\Core\Exception\TokenFactoryException;

class TokenFactory implements TokenFactoryInterface
{

    /**
     *
     * @var StorageInterface
     */
    protected $tokenStorage;

    /**
     *
     * @var StorageRegistryInterface
     */
    protected $storageRegistry;

    /**
     *
     * @var string
     */
    protected $tokenParameter;

    /**
     *
     * @var string
     */
    protected $baseUrl;

    /**
     *
     * @param StorageInterface $tokenStorage
     * @param StorageRegistryInterface $storageRegistry
     * @param string $tokenParameter
     */
    public function __construct(StorageInterface $tokenStorage, StorageRegistryInterface $storageRegistry, $tokenParameter = 'payum_token')
    {
        $this->tokenStorage = $tokenStorage;
        $this->storageRegistry = $storageRegistry;
        $this->tokenParameter = (string) $tokenParameter;
    }

    /**
     * {@inheritDoc}
     */
    public function createToken($paymentName, $model, $targetPath, array $targetParameters = null, $afterPath = null, array $afterParameters = null)
    {
        /**
         * @var TokenInterface $token
         */
        $token = $this->tokenStorage->createModel();

        $token->setHash($this->generateHash());
        $token->setPaymentName($paymentName);
        $token->setDetails($this->getIdentificator($model));

        $targetParameters[$this->tokenParameter] = $token->getHash();
        $token->setTargetUrl($this->generateUrl($targetPath, $targetParameters));
        $afterPath === null || $token->setAfterUrl($this->generateUrl($afterPath, $afterParameters));

        $this->tokenStorage->updateModel($token);

        return $token;
    }

    /**
     * {@inheritDoc}
     */
    public function getTokenStorage()
    {
        return $this->tokenStorage;
    }

    /**
     *
     * @return string
     */
    protected function generateHash()
    {
        return Random::generateToken();
    }

    /**
     *
     * @param object|null $model
     * @return \Payum\Core\Model\Identificator
     */
    protected function getIdentificator($model)
    {
        if ($model instanceof Identificator) {
            return $model;
        }

        if (null !== $model) {
            return $this->storageRegistry->getStorage($model)->getIdentificator($model);
        }

        return $model;
    }

    /**
     *
     * @param string $path
     * @param array $parameters
     *
     * @return string
     */
    protected function generateUrl($path, array $parameters = null)
    {
        if (preg_match('@^[a-z+]+:@i', ltrim($path))) {
            $url = Url::createFromUrl($path);
        } elseif (isset($this->baseUrl)) {
            $url = Url::createFromUrl($this->baseUrl);
            $url->getPath()->append($path);
        } else {
            throw TokenFactoryException::couldNotGenerateUrlFor($path, $parameters);
        }
        $url->getQuery()->modify((array) $parameters);
        return (string) $url;
    }

    /**
     *
     * @param string $baseUrl
     */
    public function setBaseUrl($baseUrl)
    {
        $this->baseUrl = (string) $baseUrl;
    }

    /**
     *
     * @return string
     */
    public function getBaseUrl()
    {
        return $this->baseUrl;
    }
}
