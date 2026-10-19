<?php

declare(strict_types=1);

namespace Payum\Core\Tests\Bc;

/**
 * One scripted run against a gateway, written in the API 1.x and 2.x share so the same script records the
 * golden output on 1.x and replays it on 2.x.
 *
 * A step is an array with an `op`:
 *
 *  - `select`: which registered action answers each probe request ({@see Recorder::probes()} plus `probes`).
 *  - `status`: GetHumanStatus and GetBinaryStatus over the `details` given.
 *  - `capture`, `authorize`, `refund`, `cancel`, `sync`, `notify`, `payout`: that request, `on` the stored
 *    `payment` or a `token` for it. `get` and `post` become the HTTP request it arrives with, `http` queues
 *    responses for `payum.http_client` and `stripe` for the Stripe SDK.
 */
final class Scenario
{
    /**
     * @param array<string, mixed> $config gateway options passed to addGateway()
     * @param list<array<string, mixed>> $steps
     * @param array<string, mixed> $details what the stored payment's details start as
     * @param list<array{0: class-string, 1?: array<string, mixed>}> $probes gateway-specific requests for `select`, built over a details array
     * @param class-string|null $factoryClass a gateway factory to register as `factory`, for one core does not ship
     */
    public function __construct(
        public readonly string $name,
        public readonly string $factory,
        public readonly array $config,
        public readonly array $steps,
        public readonly array $details = [],
        public readonly array $probes = [],
        public readonly int $amount = 1234,
        public readonly string $currency = 'EUR',
        public readonly ?string $factoryClass = null,
    ) {
    }
}
