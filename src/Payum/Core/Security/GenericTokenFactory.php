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
    public function createToken($gatewayName, $model, $targetPath, array $targetParameters = array(), $afterPath = null, array $afterParameters = array())
    {
        return $this->tokenFactory->createToken(
            $gatewayName,
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
    public function createCaptureToken($gatewayName, $model, $afterPath, array $afterParameters = array())
    {
        $capturePath = $this->getPath('capture');

        $afterToken = $this->createToken($gatewayName, $model, $afterPath, $afterParameters);

        return $this->createToken(
            $gatewayName,
            $model,
            $capturePath,
            array(),
            $afterToken->getTargetUrl()
        );
    }

    /**
     * {@inheritDoc}
     */
    public function createAuthorizeToken($gatewayName, $model, $afterPath, array $afterParameters = array())
    {
        $authorizePath = $this->getPath('authorize');

        $afterToken = $this->createToken($gatewayName, $model, $afterPath, $afterParameters);

        return $this->createToken($gatewayName, $model, $authorizePath, array(), $afterToken->getTargetUrl());
    }

    /**
     * {@inheritDoc}
     */
    public function createRefundToken($gatewayName, $model, $afterPath = null, array $afterParameters = array())
    {
        $refundPath = $this->getPath('refund');

        $afterUrl = null;
        if ($afterPath) {
            $afterUrl = $this->createToken($gatewayName, $model, $afterPath, $afterParameters)->getTargetUrl();
        }

        return $this->createToken($gatewayName, $model, $refundPath, array(), $afterUrl);
    }

    /**
     * {@inheritDoc}
     */
    public function createNotifyToken($gatewayName, $model = null)
    {
        return $this->createToken($gatewayName, $model, $this->getPath('notify'));
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
