<?php
namespace Payum\Core\Tests\Mocks\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as Mongo;
use Payum\Core\Tests\Mocks\Model\TestModel as BaseTestModel;

/**
 * @Mongo\Document
 */
class TestModel extends BaseTestModel
{
    /**
     * @Mongo\Id
     *
     * @var integer $id
     */
    protected $id;

    /**
     * @Mongo\Field(name="price")
     */
    protected $price;

    /**
     * @Mongo\Field(name="currency")
     */
    protected $currency;
}
