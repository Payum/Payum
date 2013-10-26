<?php
namespace Payum\Examples\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as Mongo;
use Payum\Model\ArrayObject as BaseArrayObject;

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