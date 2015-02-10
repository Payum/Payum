<?php
namespace Payum\Core\Security;

use Payum\Core\Exception\LogicException;

class GenericTokenFactory implements GenericTokenFactoryInterface
{
    /**
     * @var TokenFactoryInterface
     */
    protected $tokenFactory;

    /**
     * @var string[]
     */
    protected $paths;

    /**
     * @param TokenFactoryInterface $tokenFactory
     * @param string[] $paths
     */
    public function __construct(TokenFactoryInterface $tokenFactory, array $paths)
    {
        $this->tokenFactory = $tokenFactory;
        $this->paths = $paths;
    }

    /**
     * {@inheritDoc}
     */
    public function createToken($paymentName, $model, $targetPath, array $targetParameters = array(), $afterPath = null, array $afterParameters = array())
    {
        return $this->tokenFactory->createToken(
            $paymentName,
            $model,
            $targetPath,
            $targetParameters,
            $afterPath,
            $afterParameters
        );
    }

    /**
     * {@inheritDoc}
     */
    public function createCaptureToken($paymentName, $model, $afterPath, array $afterParameters = array())
    {
        $capturePath = $this->getPath('capture');

        $afterToken = $this->createToken($paymentName, $model, $afterPath, $afterParameters);

        return $this->createToken(
            $paymentName,
            $model,
            $capturePath,
            array(),
            $afterToken->getTargetUrl()
        );
    }

    /**
     * {@inheritDoc}
     */
    public function createAuthorizeToken($paymentName, $model, $afterPath, array $afterParameters = array())
    {
        $authorizePath = $this->getPath('authorize');

        $afterToken = $this->createToken($paymentName, $model, $afterPath, $afterParameters);

        return $this->createToken($paymentName, $model, $authorizePath, array(), $afterToken->getTargetUrl());
    }

    /**
     * {@inheritDoc}
     */
    public function createRefundToken($paymentName, $model, $afterPath = null, array $afterParameters = array())
    {
        $refundPath = $this->getPath('refund');

        $afterUrl = null;
        if ($afterPath) {
            $afterUrl = $this->createToken($paymentName, $model, $afterPath, $afterParameters)->getTargetUrl();
        }

        return $this->createToken($paymentName, $model, $refundPath, array(), $afterUrl);
    }

    /**
     * {@inheritDoc}
     */
    public function createNotifyToken($paymentName, $model = null)
    {
        return $this->createToken($paymentName, $model, $this->getPath('notify'));
    }

    /**
     * @param string $name
     *
     * @return string
     */
    protected function getPath($name)
    {
        if (empty($this->paths[$name])) {
            throw new LogicException(sprintf(
                'The path "%s" is not found. Possible paths are %s',
                $name,
                implode(', ', array_keys($this->paths))
            ));
        }

        return $this->paths[$name];
    }
}
