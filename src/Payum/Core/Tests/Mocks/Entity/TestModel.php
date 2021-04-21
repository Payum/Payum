<?php
namespace Payum\Core\Tests\Mocks\Entity;

use Doctrine\ORM\Mapping as ORM;
use Payum\Core\Tests\Mocks\Model\TestModel as BaseTestModel;

/**
 * @ORM\Entity
 */
class TestModel extends BaseTestModel
{
    /**
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     *
     * @var integer $id
     */
    protected $id;

    /**
     * @ORM\Column(name="price", type="string", nullable=true)
     */
    protected $price;

    /**
     * @ORM\Column(name="currency", type="string", nullable=true)
     */
    protected $currency;
}
