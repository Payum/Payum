<?php

declare(strict_types=1);

namespace Payum\Core\Tests\Mocks\Entity;

use Doctrine\ORM\Mapping as ORM;
use Payum\Core\Model\Customer as BaseCustomer;

#[ORM\Entity]
#[ORM\Table(name: 'customers')]
class Customer extends BaseCustomer
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
