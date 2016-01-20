<?php
namespace Payum\Core\Tests\Functional\Bridge\Doctrine;

use Doctrine\Common\Cache\ArrayCache;
use Doctrine\Common\Persistence\Mapping\Driver\MappingDriver;
use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\Configuration;
use Doctrine\MongoDB\Connection;
use Doctrine\ODM\MongoDB\Types\Type;
use Payum\Core\Tests\SkipOnPhp7Trait;

abstract class BaseMongoTest extends \PHPUnit_Framework_TestCase
{
    use SkipOnPhp7Trait;

    /**
     * @var DocumentManager
     */
    protected $dm;

    public function setUp()
    {
        $this->skipTestsIfPhp7();


        if (false == (class_exists(\MongoId::class) && class_exists(Connection::class))) {
            $this->markTestSkipped('Either mongo extension or\and doctrine\mongo-odm are not installed.');
        }

        Type::hasType('object') ?
            Type::overrideType('object', 'Payum\Core\Bridge\Doctrine\Types\ObjectType') :
            Type::addType('object', 'Payum\Core\Bridge\Doctrine\Types\ObjectType')
        ;

        $config = new Configuration();
        $config->setProxyDir(\sys_get_temp_dir());
        $config->setProxyNamespace('PayumTestsProxies');
        $config->setHydratorDir(\sys_get_temp_dir());
        $config->setHydratorNamespace('PayumTestsHydrators');
        $config->setMetadataDriverImpl($this->getMetadataDriverImpl($config));
        $config->setMetadataCacheImpl(new ArrayCache());
        $config->setDefaultDB('payum_tests');

        $connection = new Connection(null, array(), $config);

        $this->dm = DocumentManager::create($connection, $config);

        foreach ($this->dm->getConnection()->selectDatabase('payum_tests')->listCollections() as $collection) {
            $collection->drop();
        }
    }

    /**
     * @param Configuration $config
     *
     * @return MappingDriver
     */
    abstract protected function getMetadataDriverImpl();
}
