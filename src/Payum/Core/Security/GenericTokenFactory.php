<?php
namespace Payum\Core\Security;

use Payum\Core\Exception\LogicException;

class GenericTokenFactory implements GenericTokenFactoryInterface
{
    protected TokenFactoryInterface $tokenFactory;

    /**
     * @var string[]
     */
    protected array $paths;

    /**
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
    public function createToken($gatewayName, $model, $targetPath, array $targetParameters = [], $afterPath = null, array $afterParameters = []): TokenInterface
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
    public function createCaptureToken(string $gatewayName, object $model, string $afterPath, array $afterParameters = []): TokenInterface
    {
        $capturePath = $this->getPath('capture');

        $afterToken = $this->createToken($gatewayName, $model, $afterPath, $afterParameters);

        return $this->createToken(
            $gatewayName,
            $model,
            $capturePath,
            [],
            $afterToken->getTargetUrl()
        );
    }

    /**
     * {@inheritDoc}
     */
    public function createAuthorizeToken(string $gatewayName, object $model, string $afterPath, array $afterParameters = []): TokenInterface
    {
        $authorizePath = $this->getPath('authorize');

        $afterToken = $this->createToken($gatewayName, $model, $afterPath, $afterParameters);

        return $this->createToken($gatewayName, $model, $authorizePath, [], $afterToken->getTargetUrl());
    }

    /**
     * {@inheritDoc}
     */
    public function createRefundToken(string $gatewayName, object $model, string $afterPath = null, array $afterParameters = []): TokenInterface
    {
        $refundPath = $this->getPath('refund');

        $afterUrl = null;
        if ($afterPath) {
            $afterUrl = $this->createToken($gatewayName, $model, $afterPath, $afterParameters)->getTargetUrl();
        }

        return $this->createToken($gatewayName, $model, $refundPath, [], $afterUrl);
    }

    public function createCancelToken($gatewayName, $model, $afterPath = null, array $afterParameters = []): TokenInterface
    {
        $cancelPath = $this->getPath('cancel');

        $afterUrl = null;
        if ($afterPath) {
            $afterUrl = $this->createToken($gatewayName, $model, $afterPath, $afterParameters)->getTargetUrl();
        }

        return $this->createToken($gatewayName, $model, $cancelPath, [], $afterUrl);
    }

    /**
     * {@inheritDoc}
     */
    public function createPayoutToken(string $gatewayName, object $model, string $afterPath, array $afterParameters = []): TokenInterface
    {
        $capturePath = $this->getPath('payout');

        $afterToken = $this->createToken($gatewayName, $model, $afterPath, $afterParameters);

        return $this->createToken(
            $gatewayName,
            $model,
            $capturePath,
            [],
            $afterToken->getTargetUrl()
        );
    }

    /**
     * {@inheritDoc}
     */
    public function createNotifyToken(string $gatewayName, object $model = null): TokenInterface
    {
        return $this->createToken($gatewayName, $model, $this->getPath('notify'));
    }

    protected function getPath(string $name): string
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
