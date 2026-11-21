<?php

declare(strict_types=1);

namespace Payum\Core\Tests\Mocks\Entity;

use Doctrine\ORM\Mapping as ORM;
use Payum\Core\Model\CustomerInterface;
use Payum\Core\Model\StoredPaymentMethodInterface;
use Payum\Core\Model\Subscription as BaseSubscription;

#[ORM\Entity]
#[ORM\Table(name: 'subscriptions')]
class Subscription extends BaseSubscription
{
    #[ORM\Id]
    #[ORM\Column(name: 'id', type: 'integer')]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    protected ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Customer::class)]
    #[ORM\JoinColumn(name: 'customer_id', nullable: true)]
    protected ?CustomerInterface $customer = null;

    #[ORM\ManyToOne(targetEntity: StoredPaymentMethod::class)]
    #[ORM\JoinColumn(name: 'stored_payment_method_id', nullable: true)]
    protected ?StoredPaymentMethodInterface $storedPaymentMethod = null;

    public function getId(): ?int
    {
        return $this->id;
    }
}
