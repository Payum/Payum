<?php
namespace Payum\Core\Tests\Mocks\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as Mongo;
use Payum\Core\Model\ArrayObject as BaseArrayObject;

/**
 * @Mongo\Document
 */
class ArrayObject extends BaseArrayObject
{
    /**
     * @Mongo\Id
     */
    protected $id;

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }
}
