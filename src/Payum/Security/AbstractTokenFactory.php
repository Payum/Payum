<?php
namespace Payum\Security;

use Payum\Registry\RegistryInterface;
use Payum\Storage\StorageInterface;

class AbstractTokenFactory implements TokenFactoryInterface
{
    /**
     * @var \Payum\Storage\StorageInterface
     */
    protected $tokenStorage;

    /**
     * @var \Payum\Registry\RegistryInterface
     */
    protected $payum;

    /**
     * @param \Payum\Storage\StorageInterface $tokenStorage
     * @param \Payum\Registry\RegistryInterface $payum
     */
    public function __construct(StorageInterface $tokenStorage, RegistryInterface $payum)
    {
        $this->tokenStorage = $tokenStorage;
        $this->payum = $payum;
    }

    /**
     * {@inheritDoc}
     */
    public function createToken($paymentName, $model, $targetPath, array $targetParameters = array(), $afterPath = null, array $afterParameters = array())
    {
        $modelStorage = $this->payum->getStorageForClass($model, $paymentName);

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
     * @param string $path
     * @param array $parameters
     *
     * @return string
     */
    abstract protected function generateUrl($path, array $parameters = array());
}