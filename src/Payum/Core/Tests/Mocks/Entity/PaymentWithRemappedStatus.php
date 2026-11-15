<?php

declare(strict_types=1);

namespace Payum\Core\Tests\Mocks\Entity;

use Doctrine\ORM\Mapping as ORM;
use Payum\Core\Model\Payment as BasePayment;

#[ORM\Entity]
#[ORM\Table(name: 'payments_with_remapped_status')]
#[ORM\AttributeOverrides([
    new ORM\AttributeOverride(
        name: 'status',
        column: new ORM\Column(name: 'state', type: 'string', length: 20, nullable: true),
    ),
])]
class PaymentWithRemappedStatus extends BasePayment
{
    #[ORM\Id]
    #[ORM\Column(name: 'id', type: 'integer')]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    protected ?int $id = null;

    public function getId(): ?int
    {
        return $this->id;
    }
}
