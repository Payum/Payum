<?php
namespace Payum\Core\Security;

use League\Url\Url;
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
     * @param string $path
     * @param array  $parameters
     *
     * @return string
     */
    abstract protected function generateUrl($path, array $parameters = array());
}
