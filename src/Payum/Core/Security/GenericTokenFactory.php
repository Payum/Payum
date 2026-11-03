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
     * @param array<string, mixed> $finalParameters
     */
    public function create(
        TokenPurpose $purpose,
        string $gatewayName,
        ?object $subject = null,
        ?string $afterPath = null,
        array $afterParameters = [],
        ?string $finalPath = null,
        array $finalParameters = [],
    ): TokenInterface {
        // Resolved first, so a purpose with no configured path fails before an after token is stored.
        $targetPath = $this->getPath($purpose->value);

        $after = fn (?string $path): string => $this->createToken($gatewayName, $subject, $path, $afterParameters, $finalPath, $finalParameters)->getTargetUrl();

        $afterUrl = match ($purpose) {
            TokenPurpose::Capture => $after($afterPath ?? $this->getPath('done')),
            TokenPurpose::Authorize, TokenPurpose::Payout => $after($afterPath),
            TokenPurpose::Refund => $afterPath ? $after($afterPath) : null,
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
    public function createCaptureToken($gatewayName, $model, $afterPath, array $afterParameters = [] /* , ?string $finalPath = null, array $finalParameters = [] */)
    {
        return $this->create(TokenPurpose::Capture, $gatewayName, $model, $afterPath, $afterParameters, ...array_slice(func_get_args(), 4));
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
    public function createAuthorizeToken($gatewayName, $model, $afterPath, array $afterParameters = [] /* , ?string $finalPath = null, array $finalParameters = [] */)
    {
        return $this->create(TokenPurpose::Authorize, $gatewayName, $model, $afterPath, $afterParameters, ...array_slice(func_get_args(), 4));
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
    public function createRefundToken($gatewayName, $model, $afterPath = null, array $afterParameters = [] /* , ?string $finalPath = null, array $finalParameters = [] */)
    {
        return $this->create(TokenPurpose::Refund, $gatewayName, $model, $afterPath, $afterParameters, ...array_slice(func_get_args(), 4));
    }

    /**
     * @param string $gatewayName
     * @param object|array<string, scalar> $model
     * @param string|null $afterPath
     * @param array<string, scalar> $afterParameters
     *
     * @return TokenInterface
     */
    public function createCancelToken($gatewayName, $model, $afterPath = null, array $afterParameters = [] /* , ?string $finalPath = null, array $finalParameters = [] */)
    {
        $cancelPath = $this->getPath('cancel');

        $afterUrl = null;
        if ($afterPath) {
            $afterUrl = $this->createToken($gatewayName, $model, $afterPath, $afterParameters, ...array_slice(func_get_args(), 4))->getTargetUrl();
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
    public function createPayoutToken($gatewayName, $model, $afterPath, array $afterParameters = [] /* , ?string $finalPath = null, array $finalParameters = [] */)
    {
        return $this->create(TokenPurpose::Payout, $gatewayName, $model, $afterPath, $afterParameters, ...array_slice(func_get_args(), 4));
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
