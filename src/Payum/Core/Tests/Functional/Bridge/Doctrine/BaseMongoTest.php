<?php

namespace Payum\Core\Tests\Functional\Bridge\Doctrine;

use Doctrine\Common\Cache\ArrayCache;
use Doctrine\ODM\MongoDB\Configuration;
use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\Types\Type;
use Doctrine\Persistence\Mapping\Driver\MappingDriver;
use MongoDB\Client;
use MongoDB\Driver\Manager;
use Payum\Core\Bridge\Doctrine\Types\ObjectType;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use function sys_get_temp_dir;

abstract class BaseMongoTest extends TestCase
{
    /**
     * @var DocumentManager
     */
    protected $dm;

    protected function setUp(): void
    {
        if (! class_exists(Client::class) || ! class_exists(Manager::class)) {
            $this->markTestSkipped('Either mongo extension or\and doctrine/mongodb-odm are not installed.');
        }

        Type::hasType('object') ?
            Type::overrideType('object', ObjectType::class) :
            Type::addType('object', ObjectType::class)
        ;

        $config = new Configuration();
        $config->setProxyDir(sys_get_temp_dir());
        $config->setProxyNamespace('PayumTestsProxies');
        $config->setHydratorDir(sys_get_temp_dir());
        $config->setHydratorNamespace('PayumTestsHydrators');
        $config->setMetadataDriverImpl($this->getMetadataDriverImpl());

        if (method_exists($config, 'setMetadataCache')) {
            $config->setMetadataCache(new ArrayAdapter());
        } else {
            $config->setMetadataCacheImpl(new ArrayCache());
        }

        $config->setDefaultDB('payum_tests');

        $this->dm = DocumentManager::create(null, $config);

        $mongoDatabase = $this->dm->getClient()->selectDatabase('payum_tests');

        foreach ($mongoDatabase->listCollections() as $collection) {
            $mongoDatabase->selectCollection($collection->getName())->drop();
        }
    }

    /**
     * @return MappingDriver
     */
    abstract protected function getMetadataDriverImpl();
}
