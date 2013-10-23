<?php
namespace Payum\Tests\Functional\Bridge\Doctrine;

use Doctrine\Common\Cache\ArrayCache;
use Doctrine\Common\Persistence\Mapping\Driver\MappingDriver;
use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\Configuration;
use Doctrine\MongoDB\Connection;
use Doctrine\ODM\MongoDB\Types\Type;

abstract class BaseMongoTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var DocumentManager
     */
    protected $dm;

    public function setUp()
    {
        $conf = new Configuration();
        $conf->setProxyDir(\sys_get_temp_dir());
        $conf->setProxyNamespace('PayumTestsProxies');
        $conf->setHydratorDir(\sys_get_temp_dir());
        $conf->setHydratorNamespace('PayumTestsHydrators');
        $conf->setMetadataDriverImpl($this->getMetadataDriverImpl());
        $conf->setMetadataCacheImpl(new ArrayCache());
        $conf->setDefaultDB('payum_tests');

        $conn = new Connection(null, array(), $conf);

        $this->dm = DocumentManager::create($conn, $conf);

        foreach ($this->dm->getConnection()->selectDatabase('payum_tests')->listCollections() as $collection) {
            $collection->drop();
        }
    }

    /**
     * @return MappingDriver
     */
    abstract protected function getMetadataDriverImpl();
}