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
     * @var StorageRegistryInterface<object>
     */
    protected StorageRegistryInterface $storageRegistry;

    /**
     * @param StorageInterface<TokenInterface> $tokenStorage
     * @param StorageRegistryInterface<object> $storageRegistry
     */
    public function __construct(StorageInterface $tokenStorage, StorageRegistryInterface $storageRegistry)
    {
        $this->tokenStorage = $tokenStorage;
        $this->storageRegistry = $storageRegistry;
    }

    /**
     * @param array<string, ?string> $targetParameters
     * @param array<string, ?string> $afterParameters
     */
    public function createToken(string $gatewayName, ?object $model, string $targetPath, array $targetParameters = [], ?string $afterPath = null, array $afterParameters = []): TokenInterface
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
            $token->setDetails($this->storageRegistry->getStorage($model::class)->identify($model));
        }

        if (str_starts_with($targetPath, 'http')) {
            $targetUri = HttpUri::createFromString($targetPath);
            $targetUri = $this->addQueryToUri($targetUri, $targetParameters);

            $token->setTargetUrl((string) $targetUri);
        } else {
            $token->setTargetUrl($this->generateUrl($targetPath, $targetParameters));
        }

        if ($afterPath && str_starts_with($afterPath, 'http')) {
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
     * @param array<string, string> $query
     */
    protected function addQueryToUri(HttpUri $uri, array $query): HttpUri
    {
        $uriQuery = Query::createFromUri($uri)->withoutEmptyPairs();

        $query = array_replace($uriQuery->params(), $query);

        return $uri->withQuery((string) Query::createFromParams($query));
    }

    abstract protected function generateUrl(string $path, array $parameters = []): string;
}
