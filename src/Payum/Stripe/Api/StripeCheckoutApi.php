<?php

declare(strict_types=1);

namespace Payum\Stripe\Api;

use Payum\Core\Exception\LogicException;
use Payum\Stripe\Config\StripeCheckoutConfig;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;

/**
 * STUB -- one API object per gateway, the only thing that talks to the PSP.
 *
 * This replaces v1's addApi() / ApiAwareInterface / api-per-action matching entirely. Two API versions
 * are simply two classes with two definitions, which is why UnsupportedApiException has nothing left to
 * do.
 *
 * Every constructor parameter is already a container entry -- the config is registered by core when the
 * gateway is built, and the PSR-18 client and PSR-17 factories come from the global container -- so this
 * class needs **no service definition whatsoever**. PHP-DI autowires it. That is the payoff of keeping
 * config out of handlers: the dependency chain is config -> api -> handler, and only the first link needs
 * declaring.
 *
 * Contrast Paypal's descriptor, which has to declare its Api by hand because the v1 Nvp\Api takes an
 * array as its first parameter and an array has no type to resolve.
 */
final class StripeCheckoutApi
{
    private const BASE_URI = 'https://api.stripe.com/v1';

    public function __construct(
        private readonly StripeCheckoutConfig $config,
        private readonly ClientInterface $httpClient,
        private readonly RequestFactoryInterface $requestFactory,
    ) {
    }

    /**
     * POST /v1/checkout/sessions
     *
     * @param array<string, mixed> $parameters
     *
     * @return array<string, mixed> the session, including its 'id' and hosted 'url'
     */
    public function createSession(array $parameters): array
    {
        return $this->send('POST', '/checkout/sessions', $parameters);
    }

    /**
     * POST /v1/refunds
     *
     * @return array<string, mixed>
     */
    public function refund(string $paymentIntentId, ?int $amount = null): array
    {
        return $this->send('POST', '/refunds', array_filter([
            'payment_intent' => $paymentIntentId,
            'amount' => $amount,
        ], static fn ($value): bool => null !== $value));
    }

    /**
     * GET /v1/checkout/sessions/{id}
     *
     * @return array<string, mixed>
     */
    public function retrieveSession(string $sessionId): array
    {
        return $this->send('GET', '/checkout/sessions/' . $sessionId);
    }

    /**
     * Every call funnels through here, which is where transport concerns belong: the secret key goes on
     * as a bearer token, and retries, logging and redaction would be PSR-18 decorators around
     * $httpClient rather than branches in this class.
     *
     * @param array<string, mixed> $parameters
     *
     * @return array<string, mixed>
     */
    private function send(string $method, string $path, array $parameters = []): array
    {
        $request = $this->requestFactory
            ->createRequest($method, self::BASE_URI . $path)
            ->withHeader('Authorization', 'Bearer ' . $this->config->getSecretKey())
            ->withHeader('Content-Type', 'application/x-www-form-urlencoded');

        throw new LogicException(sprintf(
            'Stub: %s %s is not implemented in this pass (%d parameters, client %s).',
            $request->getMethod(),
            (string) $request->getUri(),
            count($parameters),
            $this->httpClient::class,
        ));
    }
}
