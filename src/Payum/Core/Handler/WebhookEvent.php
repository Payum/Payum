<?php

declare(strict_types=1);

namespace Payum\Core\Handler;

use Payum\Core\Result\Acknowledgement;

/**
 * A message a PSP sent to say something happened, after a gateway has decided what to make of it.
 *
 * Produced by {@see NotifyHandlerInterface::verify()} and handed straight to handle(), so a handler
 * cannot reach the payload without that decision having been made.
 *
 * It is plain data, so it can be serialized and handled elsewhere, such as on a queue. The acknowledgement
 * is what to answer the PSP with in the meantime, when the handler that would normally name it has not run.
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
        public readonly ?Acknowledgement $acknowledgement = null,
        public readonly ?string $reference = null,
        public readonly ?string $merchantReference = null,
    ) {
    }

    /**
     * The message is genuine.
     *
     * @param array<string, mixed> $payload
     * @param string|null $id the PSP's own event id, which is how a redelivery is recognised
     * @param string|null $type the PSP's own event name, such as 'payment_intent.succeeded'
     * @param Acknowledgement|null $acknowledgement what to answer the PSP with when handling happens elsewhere
     * @param string|null $reference the PSP's id for what the message is about, such as 'pi_…' or 'sub_…'
     * @param string|null $merchantReference what the gateway sent the PSP as metadata, echoed back
     */
    public static function verified(
        array $payload,
        ?string $id = null,
        ?string $type = null,
        ?Acknowledgement $acknowledgement = null,
        ?string $reference = null,
        ?string $merchantReference = null,
    ): self {
        return new self(true, $payload, $id, $type, $acknowledgement, $reference, $merchantReference);
    }

    /**
     * The PSP offers nothing to check, so the payload is taken on trust.
     *
     * A handler returning this should not act on what the message says. Re-read the state from the PSP
     * instead, by dispatching a {@see \Payum\Core\Command\SyncCommand}. Its references are still used to
     * find the payment it is about.
     *
     * @param array<string, mixed> $payload
     */
    public static function unverified(
        array $payload,
        ?string $id = null,
        ?string $type = null,
        ?Acknowledgement $acknowledgement = null,
        ?string $reference = null,
        ?string $merchantReference = null,
    ): self {
        return new self(false, $payload, $id, $type, $acknowledgement, $reference, $merchantReference);
    }

    public function isVerified(): bool
    {
        return $this->verified;
    }
}
