<?php
namespace Payum\Core\Tests\Mocks\Document;

use Payum\Core\Model\GatewayConfig as BaseGatewayConfig;
use Doctrine\ODM\MongoDB\Mapping\Annotations as Mongo;

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
