<?php

declare(strict_types=1);

namespace Payum\Core\Security;

interface TokenIssuerInterface
{
    /**
     * Mints a token whose target URL runs the operation the purpose names on the subject.
     *
     * The after path is where the customer lands once the operation is done. A notify token has none.
     *
     * @param array<string, mixed> $afterParameters
     */
    public function create(
        TokenPurpose $purpose,
        string $gatewayName,
        ?object $subject = null,
        ?string $afterPath = null,
        array $afterParameters = [],
    ): TokenInterface;
}
