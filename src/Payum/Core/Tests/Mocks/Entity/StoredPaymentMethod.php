<?php

declare(strict_types=1);

namespace Payum\Core\Tests\Mocks\Entity;

use Doctrine\ORM\Mapping as ORM;
use Payum\Core\Model\CustomerInterface;
use Payum\Core\Model\StoredPaymentMethod as BaseStoredPaymentMethod;

#[ORM\Entity]
#[ORM\Table(name: 'stored_payment_methods')]
class StoredPaymentMethod extends BaseStoredPaymentMethod
{
    #[ORM\Id]
    #[ORM\Column(name: 'id', type: 'integer')]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    protected ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Customer::class)]
    #[ORM\JoinColumn(name: 'customer_id', nullable: true)]
    protected ?CustomerInterface $customer = null;

    public function getId(): ?int
    {
        return $this->id;
    }
}
