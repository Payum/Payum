<?php

declare(strict_types=1);

namespace Payum\Core\Model;

use DateTimeImmutable;

/**
 * The first result of an operation that a caller gave an idempotency key, kept so that a repeat of the
 * key gets the same answer.
 */
class IdempotencyRecord
{
    protected string $scope;

    protected string $fingerprint;

    /**
     * @var array<string, mixed>
     */
    protected array $result = [];

    protected DateTimeImmutable $expiresAt;

    public function getScope(): string
    {
        return $this->scope;
    }

    /**
     * @param string $scope a hash of the gateway name and the key
     */
    public function setScope(string $scope): void
    {
        $this->scope = $scope;
    }

    public function getFingerprint(): string
    {
        return $this->fingerprint;
    }

    /**
     * @param string $fingerprint a hash of what the command asked for, to tell a repeat from a reused key
     */
    public function setFingerprint(string $fingerprint): void
    {
        $this->fingerprint = $fingerprint;
    }

    /**
     * @return array<string, mixed>
     */
    public function getResult(): array
    {
        return $this->result;
    }

    /**
     * @param array<string, mixed> $result
     */
    public function setResult(array $result): void
    {
        $this->result = $result;
    }

    public function getExpiresAt(): DateTimeImmutable
    {
        return $this->expiresAt;
    }

    public function setExpiresAt(DateTimeImmutable $expiresAt): void
    {
        $this->expiresAt = $expiresAt;
    }
}
