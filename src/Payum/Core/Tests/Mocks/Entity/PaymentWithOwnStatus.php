<?php

declare(strict_types=1);

namespace Payum\Core\Tests\Mocks\Entity;

use Doctrine\ORM\Mapping as ORM;
use Payum\Core\Model\Payment as BasePayment;
use Payum\Core\Result\PaymentStatus;

#[ORM\Entity]
#[ORM\Table(name: 'payments_with_own_status')]
class PaymentWithOwnStatus extends BasePayment
{
    #[ORM\Id]
    #[ORM\Column(name: 'id', type: 'integer')]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    protected ?int $id = null;

    #[ORM\Column(type: 'string', enumType: PaymentStatus::class)]
    protected PaymentStatus $status = PaymentStatus::New;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getStatus(): PaymentStatus
    {
        return $this->status;
    }

    public function setStatus(PaymentStatus $status): void
    {
        $this->status = $status;
    }
}
