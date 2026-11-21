<?php

declare(strict_types=1);

namespace Payum\Core\Tests\Mocks\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as Mongo;
use Payum\Core\Model\StoredPaymentMethod as BaseStoredPaymentMethod;

/**
 * @Mongo\Document
 */
class StoredPaymentMethod extends BaseStoredPaymentMethod
{
    /**
     * @Mongo\Id
     */
    protected mixed $id = null;

    public function getId(): mixed
    {
        return $this->id;
    }
}
