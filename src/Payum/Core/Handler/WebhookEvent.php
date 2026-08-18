<?php

declare(strict_types=1);

namespace Payum\Core\Handler;

/**
 * A message a PSP sent to say something happened, after a gateway has decided what to make of it.
 *
 * Produced by {@see NotifyHandlerInterface::verify()} and handed straight to handle(), so a handler
 * cannot reach the payload without that decision having been made.
 */
final class WebhookEvent
{
    /**
     * @param array<string, mixed> $payload
     */
    private function __construct(
        private readonly bool $verified,
        public readonly array $payload,
        public readonly ?string $id = null,
        public readonly ?string $type = null,
    ) {
    }

    /**
     * The message is genuine.
     *
     * @param array<string, mixed> $payload
     * @param string|null $id the PSP's own event id, which is how a redelivery is recognised
     * @param string|null $type the PSP's own event name, such as 'payment_intent.succeeded'
     */
    public static function verified(array $payload, ?string $id = null, ?string $type = null): self
    {
        return new self(true, $payload, $id, $type);
    }

    /**
     * The PSP offers nothing to check, so the payload is taken on trust.
     *
     * A handler returning this should not act on what the message says. Re-read the state from the PSP
     * instead, by dispatching a {@see \Payum\Core\Command\SyncCommand}.
     *
     * @param array<string, mixed> $payload
     */
    public static function unverified(array $payload, ?string $id = null, ?string $type = null): self
    {
        return new self(false, $payload, $id, $type);
    }

    public function isVerified(): bool
    {
        return $this->verified;
    }
}
