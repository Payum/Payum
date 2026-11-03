<?php

namespace Payum\Core\Tests\Mocks\Entity;

use Doctrine\ORM\Mapping as ORM;
use Payum\Core\Tests\Mocks\Model\TestModel as BaseTestModel;

#[ORM\Entity]
class TestModel extends BaseTestModel
{
    /**
     * @var int
     */
    #[ORM\Id]
    #[ORM\Column(name: 'id', type: 'integer')]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    protected $id;

    #[ORM\Column(name: 'price', type: 'string', nullable: true)]
    protected $price;

    #[ORM\Column(name: 'currency', type: 'string', nullable: true)]
    protected $currency;
}
