<?php

declare(strict_types=1);

namespace Payum\Core\Tests\Mocks\Entity;

use Doctrine\ORM\Mapping as ORM;
use Payum\Core\Model\CustomerInterface;
use Payum\Core\Model\CustomerTrait;

/**
 * The application's own user as the customer, as docs/stored-payment-methods.md shows it.
 */
#[ORM\Entity]
#[ORM\Table(name: 'users')]
class User implements CustomerInterface
{
    use CustomerTrait;

    /**
     * @var array<string, string>
     */
    #[ORM\Column(type: 'json')]
    protected array $gatewayCustomerIds = [];

    #[ORM\Id]
    #[ORM\Column(type: 'integer')]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    protected ?int $id = null;

    #[ORM\Column(type: 'string')]
    private string $email = '';

    public function getCustomerId(): string
    {
        return (string) $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function getName(): ?string
    {
        return null;
    }

    /**
     * @return array{}
     */
    // @phpstan-ignore method.childReturnType, method.childReturnType
    public function getDetails(): array
    {
        return [];
    }

    public function setDetails($details): void
    {
    }
}
