<?php

declare(strict_types=1);

namespace Payum\Core\Handler;

use Payum\Core\Command\ChargeReason;
use Payum\Core\Command\Initiator;

final class StoredCredential
{
    public function __construct(
        public readonly Initiator $initiator,
        public readonly ChargeReason $reason,
        public readonly bool $isFirst,
        public readonly ?string $previousNetworkTransactionId = null,
    ) {
    }
}
