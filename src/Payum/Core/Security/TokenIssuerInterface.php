<?php

declare(strict_types=1);

namespace Payum\Core\Security;

interface TokenIssuerInterface
{
    /**
     * Mints a token whose target URL runs the operation the purpose names on the subject.
     *
     * The after path is where the customer lands once the operation is done. A notify token has none.
     * When the after path is itself a Payum operation, such as a capture that follows an authorize, the
     * final path is where that operation sends the customer once it is done.
     *
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
    ): TokenInterface;
}
