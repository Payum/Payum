<?php

namespace Payum\Stripe\Config;

use Assert\Assert;
use LogicException;
use Payum\Core\Config\GatewayConfig;
use Payum\Stripe\StripeCheckoutGateway;

final class StripeCheckoutConfig implements GatewayConfig
{
    public function __construct(
        private string $secretKey,
        private string $publishableKey
    ) {
        $this->validateConfig($this->getConfig());
    }

    public function getSecretKey(): string
    {
        return $this->secretKey;
    }

    public function getPublishableKey(): string
    {
        return $this->publishableKey;
    }

    public function withSecretKey(string $secretKey): self
    {
        return new self($secretKey, $this->publishableKey);
    }

    public function withPublishableKey(string $publishableKey): self
    {
        return new self($this->secretKey, $publishableKey);
    }

    /**
     * @param array{secret_key?: string, publishable_key?: string} $config
     */
    public static function fromArray(array $config): self
    {
        return new self(
            $config['secret_key'],
            $config['publishable_key']
        );
    }

    public function getGatewayName(): string
    {
        return 'stripe_checkout';
    }

    public function setGatewayName($gatewayName): void
    {
        throw new LogicException('Cannot set gateway name for this config.');
    }

    public function getFactoryName(): string
    {
        return 'stripe_checkout';
    }

    public function setFactoryName($name): void
    {
        throw new LogicException('Cannot set factory name for this config.');
    }

    /**
     * @param array{secret_key?: string, publishable_key?: string} $config
     */
    public function setConfig(array $config): self
    {
        $this->validateConfig($config);

        $this->secretKey = $config['secret_key'];
        $this->publishableKey = $config['publishable_key'];

        return $this;
    }

    /**
     * @return array{secret_key: string, publishable_key: string}
     */
    public function getConfig(): array
    {
        return [
            'secret_key' => $this->secretKey,
            'publishable_key' => $this->publishableKey,
        ];
    }

    public function getGatewayClass(): string
    {
        return StripeCheckoutGateway::class;
    }

    /**
     * @param array{secret_key?: string, publishable_key?: string} $config
     */
    private function validateConfig(array $config): void
    {
        /*Assert::lazy()
            ->that($config)
            ->keyExists('secret_key')
            ->keyExists('publishable_key')
            ->that($config['secret_key'])
            ->string()
            ->that($config['publishable_key'])
            ->string()
            ->verifyNow();*/
    }
}
