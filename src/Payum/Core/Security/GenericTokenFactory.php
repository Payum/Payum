<?php

namespace Payum\Core\Security;

use Payum\Core\Exception\LogicException;

class GenericTokenFactory implements GenericTokenFactoryInterface, TokenIssuerInterface
{
    /**
     * @param array<string, string> $paths
     */
    public function __construct(
        protected TokenFactoryInterface $tokenFactory,
        protected array $paths
    ) {
    }

    /**
     * @param string $gatewayName
     * @param object|array<string, scalar> $model
     * @param string|null $targetPath
     * @param array<string, scalar> $targetParameters
     * @param string|null $afterPath
     * @param array<string, scalar> $afterParameters
     *
     * @return TokenInterface
     */
    public function createToken($gatewayName, $model, $targetPath, array $targetParameters = [], $afterPath = null, array $afterParameters = [])
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
     * @param array<string, mixed> $afterParameters
     */
    public function create(
        TokenPurpose $purpose,
        string $gatewayName,
        ?object $subject = null,
        ?string $afterPath = null,
        array $afterParameters = [],
    ): TokenInterface {
        // Resolved first, so a purpose with no configured path fails before an after token is stored.
        $targetPath = $this->getPath($purpose->value);

        $afterUrl = match ($purpose) {
            TokenPurpose::Capture => $this->createToken($gatewayName, $subject, $afterPath ?? $this->getPath('done'), $afterParameters)->getTargetUrl(),
            TokenPurpose::Authorize, TokenPurpose::Payout => $this->createToken($gatewayName, $subject, $afterPath, $afterParameters)->getTargetUrl(),
            TokenPurpose::Refund => $afterPath ? $this->createToken($gatewayName, $subject, $afterPath, $afterParameters)->getTargetUrl() : null,
            TokenPurpose::Notify => null,
        };

        return $this->createToken($gatewayName, $subject, $targetPath, [], $afterUrl);
    }

    /**
     * @deprecated since 2.0, removed in 3.0. Use {@see create()} with {@see TokenPurpose::Capture}.
     *
     * @param string $gatewayName
     * @param object|null $model
     * @param string|null $afterPath
     * @param array<string, scalar> $afterParameters
     *
     * @return TokenInterface
     */
    public function createCaptureToken($gatewayName, $model, $afterPath, array $afterParameters = [])
    {
        return $this->create(TokenPurpose::Capture, $gatewayName, $model, $afterPath, $afterParameters);
    }

    /**
     * @deprecated since 2.0, removed in 3.0. Use {@see create()} with {@see TokenPurpose::Authorize}.
     *
     * @param string $gatewayName
     * @param object|null $model
     * @param string|null $afterPath
     * @param array<string, scalar> $afterParameters
     *
     * @return TokenInterface
     */
    public function createAuthorizeToken($gatewayName, $model, $afterPath, array $afterParameters = [])
    {
        return $this->create(TokenPurpose::Authorize, $gatewayName, $model, $afterPath, $afterParameters);
    }

    /**
     * @deprecated since 2.0, removed in 3.0. Use {@see create()} with {@see TokenPurpose::Refund}.
     *
     * @param string $gatewayName
     * @param object|null $model
     * @param string|null $afterPath
     * @param array<string, scalar> $afterParameters
     *
     * @return TokenInterface
     */
    public function createRefundToken($gatewayName, $model, $afterPath = null, array $afterParameters = [])
    {
        return $this->create(TokenPurpose::Refund, $gatewayName, $model, $afterPath, $afterParameters);
    }

    /**
     * @param string $gatewayName
     * @param object|array<string, scalar> $model
     * @param string|null $afterPath
     * @param array<string, scalar> $afterParameters
     *
     * @return TokenInterface
     */
    public function createCancelToken($gatewayName, $model, $afterPath = null, array $afterParameters = [])
    {
        $cancelPath = $this->getPath('cancel');

        $afterUrl = null;
        if ($afterPath) {
            $afterUrl = $this->createToken($gatewayName, $model, $afterPath, $afterParameters)->getTargetUrl();
        }

        return $this->createToken($gatewayName, $model, $cancelPath, [], $afterUrl);
    }

    /**
     * @deprecated since 2.0, removed in 3.0. Use {@see create()} with {@see TokenPurpose::Payout}.
     *
     * @param string $gatewayName
     * @param object|null $model
     * @param string|null $afterPath
     * @param array<string, scalar> $afterParameters
     *
     * @return TokenInterface
     */
    public function createPayoutToken($gatewayName, $model, $afterPath, array $afterParameters = [])
    {
        return $this->create(TokenPurpose::Payout, $gatewayName, $model, $afterPath, $afterParameters);
    }

    /**
     * @deprecated since 2.0, removed in 3.0. Use {@see create()} with {@see TokenPurpose::Notify}.
     *
     * @param string $gatewayName
     * @param object|null $model
     *
     * @return TokenInterface
     */
    public function createNotifyToken($gatewayName, $model = null)
    {
        return $this->create(TokenPurpose::Notify, $gatewayName, $model);
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
