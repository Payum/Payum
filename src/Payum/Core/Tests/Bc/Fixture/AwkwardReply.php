<?php

declare(strict_types=1);

namespace Payum\Core\Tests\Bc\Fixture;

use Payum\Core\Reply\Base;

/**
 * A reply core has never heard of, the way third-party gateways define their own.
 */
final class AwkwardReply extends Base
{
    /**
     * @param array<string, mixed> $payload
     */
    public function __construct(
        private readonly array $payload,
    ) {
        parent::__construct();
    }

    /**
     * @return array<string, mixed>
     */
    public function getPayload(): array
    {
        return $this->payload;
    }
}
