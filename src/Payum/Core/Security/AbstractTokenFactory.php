<?php
namespace Payum\Core\Security;

use League\Uri\Http as HttpUri;
use League\Uri\Components\Query;
use League\Uri\QueryString;
use Payum\Core\Registry\StorageRegistryInterface;
use Payum\Core\Security\Util\Random;
use Payum\Core\Storage\IdentityInterface;
use Payum\Core\Storage\StorageInterface;

abstract class AbstractTokenFactory implements TokenFactoryInterface
{
    public function __construct(protected StorageInterface $tokenStorage, protected StorageRegistryInterface $storageRegistry)
    {
    }

    /**
     * {@inheritDoc}
     */
    public function createToken(string $gatewayName, $model, $targetPath, array $targetParameters = [], $afterPath = null, array $afterParameters = []): TokenInterface
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

    protected function addQueryToUri(HttpUri $uri, array $query): HttpUri
    {
        $uriQuery = Query::createFromUri($uri)->withoutEmptyPairs();

        $query = array_replace($uriQuery->params(), $query);

        return $uri->withQuery((string) Query::createFromParams($query));
    }

    abstract protected function generateUrl(string $path, array $parameters = []): string;
}
