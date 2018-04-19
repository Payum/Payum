<?php
namespace Payum\Core\Tests\Functional\Bridge\Doctrine;

use Doctrine\Common\Cache\ArrayCache;
use Doctrine\Persistence\Mapping\Driver\MappingDriver;
use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\Configuration;
use Doctrine\ODM\MongoDB\Types\Type;
use PHPUnit\Framework\TestCase;

abstract class BaseMongoTest extends TestCase
{
    /**
     * @var DocumentManager
     */
    protected $dm;

    public function setUp(): void
    {
        if (false === (class_exists(\MongoDB\Client::class))) {
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

        $this->dm = DocumentManager::create(null, $config);

        $mongoDatabase = $this->dm->getClient()->selectDatabase('payum_tests');

        foreach ($mongoDatabase->listCollections() as $collection) {
            $mongoDatabase->selectCollection($collection->getName())->drop();
        }
    }

    /**
     * @param Configuration $config
     *
     * @return MappingDriver
     */
    abstract protected function getMetadataDriverImpl();
}
