<?php

declare(strict_types=1);

namespace Payum\Core\Legacy;

use Payum\Core\Security\GenericTokenFactoryInterface;
use Payum\Core\Security\TokenInterface;
use Payum\Core\Security\TokenIssuerInterface;
use Payum\Core\Security\TokenPurpose;

/**
 * Issues tokens through a generic token factory that does not implement {@see TokenIssuerInterface}, such
 * as one an application wrote against 1.x.
 *
 * @internal
 */
final class GenericTokenFactoryIssuer implements TokenIssuerInterface
{
    private function __construct(
        private readonly GenericTokenFactoryInterface $factory,
    ) {
    }

    public static function wrap(GenericTokenFactoryInterface $factory): TokenIssuerInterface
    {
        return $factory instanceof TokenIssuerInterface ? $factory : new self($factory);
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
        // A 1.x factory declares four arguments, so the final path is only passed when there is one.
        $arguments = [$gatewayName, $subject, $afterPath, $afterParameters];
        if (null !== $finalPath) {
            $arguments[] = $finalPath;
            $arguments[] = $finalParameters;
        }

        return match ($purpose) {
            TokenPurpose::Authorize => $this->factory->createAuthorizeToken(...$arguments),
            TokenPurpose::Capture => $this->factory->createCaptureToken(...$arguments),
            TokenPurpose::Refund => $this->factory->createRefundToken(...$arguments),
            TokenPurpose::Payout => $this->factory->createPayoutToken(...$arguments),
            TokenPurpose::Notify => $this->factory->createNotifyToken($gatewayName, $subject),
        };
    }
}
