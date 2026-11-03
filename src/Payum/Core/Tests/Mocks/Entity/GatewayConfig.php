<?php

namespace Payum\Core\Tests\Mocks\Entity;

use Doctrine\ORM\Mapping as ORM;
use Payum\Core\Model\GatewayConfig as BaseGatewayConfig;

#[ORM\Entity]
class GatewayConfig extends BaseGatewayConfig
{
    /**
     * @var int
     */
    #[ORM\Id]
    #[ORM\Column(name: 'id', type: 'integer')]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    protected $id;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }
}
