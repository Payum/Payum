<?php

namespace Payum\Core\Tests\Mocks\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as Mongo;
use Payum\Core\Model\IdempotencyRecord as BaseIdempotencyRecord;

/**
 * @Mongo\Document
 */
class IdempotencyRecord extends BaseIdempotencyRecord
{
}
