<?php

namespace Payum\Core\Security;

use League\Uri\Components\Query;
use League\Uri\Http as HttpUri;
use Payum\Core\Registry\StorageRegistryInterface;
use Payum\Core\Security\Util\Random;
use Payum\Core\Storage\IdentityInterface;
use Payum\Core\Storage\StorageInterface;

abstract class AbstractTokenFactory implements TokenFactoryInterface
{
    /**
     * @var StorageInterface<TokenInterface>
     */
    protected StorageInterface $tokenStorage;

    /**
     * @var StorageRegistryInterface<StorageInterface<TokenInterface>>
     */
    protected StorageRegistryInterface $storageRegistry;

    /**
     * @param StorageInterface<TokenInterface> $tokenStorage
     * @param StorageRegistryInterface<StorageInterface<TokenInterface>> $storageRegistry
     */
    public function __construct(StorageInterface $tokenStorage, StorageRegistryInterface $storageRegistry)
    {
        $this->tokenStorage = $tokenStorage;
        $this->storageRegistry = $storageRegistry;
    }

    public function createToken($gatewayName, $model, $targetPath, array $targetParameters = [], $afterPath = null, array $afterParameters = []): TokenInterface
    {
        /** @var TokenInterface $token */
        $token = $this->tokenStorage->create();
        $token->setHash($token->getHash() ?: Random::generateToken());

        $targetParameters = array_replace([
            'payum_token' => $token->getHash(),
        ], $targetParameters);

        $token->setGatewayName($gatewayName);

        if ($model instanceof IdentityInterface) {
            $token->setDetails($model);
        } elseif (null !== $model) {
            $token->setDetails($this->storageRegistry->getStorage($model)->identify($model));
        }

        if (str_starts_with($targetPath, 'http')) {
            $targetUri = self::createUri($targetPath);
            $targetUri = $this->addQueryToUri($targetUri, $targetParameters);

            $token->setTargetUrl((string) $targetUri);
        } else {
            $token->setTargetUrl($this->generateUrl($targetPath, $targetParameters));
        }

        if ($afterPath && str_starts_with($afterPath, 'http')) {
            $afterUri = self::createUri($afterPath);
            $afterUri = $this->addQueryToUri($afterUri, $afterParameters);

            $token->setAfterUrl((string) $afterUri);
        } elseif ($afterPath) {
            $token->setAfterUrl($this->generateUrl($afterPath, $afterParameters));
        }

        $this->tokenStorage->update($token);

        return $token;
    }

    protected function addQueryToUri(HttpUri $uri, array $query): HttpUri
    {
        $fromUri = self::uriFactory(Query::class, 'fromUri', 'createFromUri');
        $uriQuery = $fromUri($uri)->withoutEmptyPairs();

        $parameters = method_exists($uriQuery, 'parameters') ? 'parameters' : 'params';
        $query = array_replace($uriQuery->{$parameters}(), $query);

        $fromParams = self::uriFactory(Query::class, 'fromVariable', 'createFromParams');

        return $uri->withQuery((string) $fromParams($query));
    }

    abstract protected function generateUrl(string $path, array $parameters = []): string;

    /**
     * league/uri 7 renamed the createFrom* factories to named constructors and made
     * the originals private, so both spellings have to be reachable while ^6.4 and
     * ^7.0 are supported. The factory is returned as a callable rather than called
     * directly so that static analysis does not resolve it against whichever major
     * happens to be installed and flag the other branch as dead.
     *
     * @param class-string $class
     */
    private static function uriFactory(string $class, string $method, string $legacyMethod): callable
    {
        return [$class, method_exists($class, $method) ? $method : $legacyMethod];
    }

    private static function createUri(string $url): HttpUri
    {
        $factory = self::uriFactory(HttpUri::class, 'new', 'createFromString');

        return $factory($url);
    }
}
