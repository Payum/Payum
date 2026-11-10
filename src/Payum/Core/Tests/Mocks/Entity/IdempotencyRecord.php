<?php

namespace Payum\Core\Tests\Mocks\Entity;

use Doctrine\ORM\Mapping as ORM;
use Payum\Core\Model\IdempotencyRecord as BaseIdempotencyRecord;

#[ORM\Entity]
class IdempotencyRecord extends BaseIdempotencyRecord
{
}
