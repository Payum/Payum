<?php

declare(strict_types=1);

namespace Payum\Core\Model;

use Traversable;

class Customer implements CustomerInterface
{
    use CustomerTrait;

    protected string $customerId = '';

    protected ?string $email = null;

    protected ?string $name = null;

    /**
     * @var array<mixed>
     */
    protected array $details = [];

    public function getCustomerId(): string
    {
        return $this->customerId;
    }

    public function setCustomerId(string $customerId): void
    {
        $this->customerId = $customerId;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): void
    {
        $this->email = $email;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): void
    {
        $this->name = $name;
    }

    public function getDetails()
    {
        return $this->details; // @phpstan-ignore return.type
    }

    /**
     * @param array<mixed>|Traversable<mixed> $details
     */
    public function setDetails($details): void
    {
        if ($details instanceof Traversable) {
            $details = iterator_to_array($details);
        }

        $this->details = $details;
    }
}
