<?php

declare(strict_types=1);

namespace Payum\Core\Result;

/**
 * What to answer the PSP with.
 *
 * Data rather than a response, so a handler names an answer without knowing which HTTP framework will
 * send it. {@see \Payum\Core\Payum::notify()} turns it into one.
 *
 * Most PSPs accept any 2xx, which is why {@see NotifyResult} leaves it null and 204 is the answer. Set
 * one when the PSP is particular: Adyen accepts only the body '[accepted]'.
 */
final class Acknowledgement
{
    /**
     * @param array<string, string> $headers
     */
    public function __construct(
        public readonly int $status = 204,
        public readonly string $body = '',
        public readonly array $headers = [],
    ) {
    }

    public static function noContent(): self
    {
        return new self();
    }

    public static function ok(string $body = ''): self
    {
        return new self(200, $body);
    }
}
