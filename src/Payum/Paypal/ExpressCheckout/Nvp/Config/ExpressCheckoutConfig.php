<?php

declare(strict_types=1);

namespace Payum\Paypal\ExpressCheckout\Nvp\Config;

use Payum\Core\Config\GatewayConfig;
use Payum\Core\Exception\LogicException;
use Payum\Paypal\ExpressCheckout\Nvp\ExpressCheckoutGateway;

/**
 * STUB -- a typed, validated replacement for the flat config array.
 *
 * Compare with v1, where these three credentials were string keys in an ArrayObject, merged through
 * populateConfig(), validated by validateNotEmpty() somewhere inside an action, and blew up at capture
 * time rather than at boot. Here a missing signature is a constructor failure with a stack trace that
 * points at the application's own wiring.
 *
 * Immutable: withSandbox() and friends return a new instance, so nothing can quietly repoint a live
 * gateway at the sandbox halfway through a request.
 */
final class ExpressCheckoutConfig implements GatewayConfig
{
    public function __construct(
        public readonly string $username,
        public readonly string $password,
        public readonly string $signature,
        public readonly bool $sandbox = false,
    ) {
        if ('' === $username || '' === $password || '' === $signature) {
            throw new LogicException('Paypal Express Checkout needs a username, a password and a signature.');
        }
    }

    /**
     * @param array{username: string, password: string, signature: string, sandbox?: bool} $config
     */
    public static function fromArray(array $config): self
    {
        return new self(
            $config['username'],
            $config['password'],
            $config['signature'],
            $config['sandbox'] ?? false,
        );
    }

    public function getGatewayClass(): string
    {
        return ExpressCheckoutGateway::class;
    }

    /**
     * The shape the existing v1 Api still expects. Exists only so the untouched Api class can be reused
     * while it is migrated; a native v2 Api would take this object directly.
     *
     * @return array{username: string, password: string, signature: string, sandbox: bool}
     */
    public function toArray(): array
    {
        return [
            'username' => $this->username,
            'password' => $this->password,
            'signature' => $this->signature,
            'sandbox' => $this->sandbox,
        ];
    }

    public function withSandbox(bool $sandbox): self
    {
        return new self($this->username, $this->password, $this->signature, $sandbox);
    }
}
