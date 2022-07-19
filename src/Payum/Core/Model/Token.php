<?php

namespace Payum\Core\Model;

use ArrayObject;
use InvalidArgumentException;
use Payum\Core\Security\TokenInterface;
use Payum\Core\Security\Util\Random;

class Token implements TokenInterface
{
    /**
     * @var ArrayObject<string, mixed>
     */
    protected ArrayObject $details;

    protected string $hash;

    protected string $afterUrl;

    protected string $targetUrl;

    protected string $gatewayName;

    public function __construct()
    {
        $this->hash = Random::generateToken();
    }

    /**
     * @return ArrayObject<string, mixed>
     */
    public function getDetails(): ArrayObject
    {
        return $this->details;
    }

    public function setDetails(mixed $details): void
    {
        if (! $details instanceof ArrayObject) {
            throw new InvalidArgumentException('Details must be an instance of ArrayObject');
        }

        $this->details = $details;
    }

    public function getHash(): string
    {
        return $this->hash;
    }

    public function setHash(string $hash): void
    {
        $this->hash = $hash;
    }

    public function getTargetUrl(): ?string
    {
        return $this->targetUrl;
    }

    public function setTargetUrl(?string $targetUrl): void
    {
        $this->targetUrl = $targetUrl;
    }

    public function getAfterUrl(): string
    {
        return $this->afterUrl;
    }

    public function setAfterUrl(string $afterUrl): void
    {
        $this->afterUrl = $afterUrl;
    }

    public function getGatewayName(): string
    {
        return $this->gatewayName;
    }

    public function setGatewayName(string $gatewayName): void
    {
        $this->gatewayName = $gatewayName;
    }
}
