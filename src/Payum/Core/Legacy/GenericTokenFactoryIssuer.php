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
     */
    public function create(
        TokenPurpose $purpose,
        string $gatewayName,
        ?object $subject = null,
        ?string $afterPath = null,
        array $afterParameters = [],
    ): TokenInterface {
        return match ($purpose) {
            TokenPurpose::Authorize => $this->factory->createAuthorizeToken($gatewayName, $subject, $afterPath, $afterParameters),
            TokenPurpose::Capture => $this->factory->createCaptureToken($gatewayName, $subject, $afterPath, $afterParameters),
            TokenPurpose::Refund => $this->factory->createRefundToken($gatewayName, $subject, $afterPath, $afterParameters),
            TokenPurpose::Payout => $this->factory->createPayoutToken($gatewayName, $subject, $afterPath, $afterParameters),
            TokenPurpose::Notify => $this->factory->createNotifyToken($gatewayName, $subject),
        };
    }
}
