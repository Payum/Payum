<?php

namespace Payum\Core\Tests\Mocks\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as Mongo;
use Payum\Core\Model\GatewayConfig as BaseGatewayConfig;

/**
 * @Mongo\Document
 */
class GatewayConfig extends BaseGatewayConfig
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
