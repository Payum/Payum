<?php
namespace Payum\Core\Security;

use League\Uri\Schemes\Http as HttpUri;
use League\Uri\Components\Query;
use League\Uri\Modifiers\MergeQuery;
use Payum\Core\Registry\StorageRegistryInterface;
use Payum\Core\Security\Util\Random;
use Payum\Core\Storage\IdentityInterface;
use Payum\Core\Storage\StorageInterface;

abstract class AbstractTokenFactory implements TokenFactoryInterface
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
     * @param StorageInterface         $tokenStorage
     * @param StorageRegistryInterface $storageRegistry
     */
    public function __construct(StorageInterface $tokenStorage, StorageRegistryInterface $storageRegistry)
    {
        $this->tokenStorage = $tokenStorage;
        $this->storageRegistry = $storageRegistry;
    }

    /**
     * {@inheritDoc}
     */
    public function createToken($gatewayName, $model, $targetPath, array $targetParameters = [], $afterPath = null, array $afterParameters = [])
    {
        /** @var TokenInterface $token */
        $token = $this->tokenStorage->create();
        $token->setHash($token->getHash() ?: Random::generateToken());

        $targetParameters = array_replace(['payum_token' => $token->getHash()], $targetParameters);

        $token->setGatewayName($gatewayName);

        if ($model instanceof IdentityInterface) {
            $token->setDetails($model);
        } elseif (null !== $model) {
            $token->setDetails($this->storageRegistry->getStorage($model)->identify($model));
        }

        if (0 === strpos($targetPath, 'http')) {
            $targetUri = HttpUri::createFromString($targetPath);
            $targetUri = $this->addQueryToUri($targetUri, $targetParameters);

            $token->setTargetUrl((string) $targetUri);
        } else {
            $token->setTargetUrl($this->generateUrl($targetPath, $targetParameters));
        }

        if ($afterPath && 0 === strpos($afterPath, 'http')) {
            $afterUri = HttpUri::createFromString($afterPath);
            $afterUri = $this->addQueryToUri($afterUri, $afterParameters);

            $token->setAfterUrl((string) $afterUri);
        } elseif ($afterPath) {
            $token->setAfterUrl($this->generateUrl($afterPath, $afterParameters));
        }

        $this->tokenStorage->update($token);

        return $token;
    }

    /**
     * @param HttpUri $uri
     * @param array $query
     *
     * @return HttpUri
     */
    protected function addQueryToUri(HttpUri $uri, array $query)
    {
        $query = array_replace($uri->query->toArray(), $query);
        $query = array_filter($query, function ($value) {
            return null !== $value;
        });

        return $uri->withQuery((string) Query::createFromArray($query));
    }

    /**
     * @param string $path
     * @param array  $parameters
     *
     * @return string
     */
    abstract protected function generateUrl($path, array $parameters = array());
}
