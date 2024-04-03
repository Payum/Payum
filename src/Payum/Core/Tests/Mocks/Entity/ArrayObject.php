<?php

namespace Payum\Core\Tests\Mocks\Entity;

use Doctrine\ORM\Mapping as ORM;
use Payum\Core\Model\ArrayObject as BaseArrayObject;

/**
 * @ORM\Entity
 * @extends BaseArrayObject<string, mixed>
 */
class ArrayObject extends BaseArrayObject
{
    /**
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     *
     * @var integer
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
