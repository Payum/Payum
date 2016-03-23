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
    public function createToken($gatewayName, $model, $targetPath, array $targetParameters = array(), $afterPath = null, array $afterParameters = array())
    {
        /** @var TokenInterface $token */
        $token = $this->tokenStorage->create();
        $token->setHash($token->getHash() ?: Random::generateToken());

        $token->setGatewayName($gatewayName);

        if ($model instanceof IdentityInterface) {
            $token->setDetails($model);
        } elseif (null !== $model) {
            $token->setDetails($this->storageRegistry->getStorage($model)->identify($model));
        }

        if (0 === strpos($targetPath, 'http')) {
            $targetUri = HttpUri::createFromString($targetPath);
            $targetUri = $targetUri->withQuery( (string) Query::createFromArray(array_replace(
                array('payum_token' => $token->getHash()),
                $targetUri->query->toArray(),
                $targetParameters
            )));
            $token->setTargetUrl((string) $targetUri);
        } else {
            $token->setTargetUrl($this->generateUrl($targetPath, array_replace(
                array('payum_token' => $token->getHash()),
                $targetParameters
            )));
        }

        if ($afterPath && 0 === strpos($afterPath, 'http')) {
            $afterUri = HttpUri::createFromString($afterPath);

            $modifier = new MergeQuery((string)Query::createFromArray($afterParameters));
            $afterUri = $modifier($afterUri);

            $token->setAfterUrl((string) $afterUri);
        } elseif ($afterPath) {
            $token->setAfterUrl($this->generateUrl($afterPath, $afterParameters));
        }

        $this->tokenStorage->update($token);

        return $token;
    }

    /**
     * @param string $path
     * @param array  $parameters
     *
     * @return string
     */
    abstract protected function generateUrl($path, array $parameters = array());
}
