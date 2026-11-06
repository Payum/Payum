<?php

declare(strict_types=1);

namespace Payum\Core\Legacy;

use Payum\Core\Exception\LogicException;
use Payum\Core\PayumBuilder;
use Payum\Core\Security\TokenInterface;
use Payum\Core\Security\TokenIssuerInterface;
use Payum\Core\Security\TokenPurpose;
use function sprintf;

/**
 * The token issuer a handler gets on a gateway a 1.x factory built by itself, without PayumBuilder, which
 * is the only place tokens are configured. Handlers that never create a token run as usual.
 *
 * @internal
 *
 * @deprecated since 2.0, removed in 3.0 along with gateway factories.
 */
final class NoTokenIssuer implements TokenIssuerInterface
{
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
        throw new LogicException(sprintf(
            'Gateway "%s" cannot create a %s token: it was built straight from its factory, which has no token storage. Build it with %s, or register a %s in its container.',
            $gatewayName,
            $purpose->value,
            PayumBuilder::class,
            TokenIssuerInterface::class,
        ));
    }
}
