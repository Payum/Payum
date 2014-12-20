<?php
namespace Payum\Core\Tests\Mocks\Entity;

use Doctrine\ORM\Mapping as ORM;
use Payum\Core\Model\Order as BaseOrder;

/**
 * @ORM\Entity
 * @ORM\Table(name="orders")
 */
class Order extends BaseOrder
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
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }
}
