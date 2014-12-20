<?php
namespace Payum\Core\Tests\Functional\Bridge\Doctrine;

use Doctrine\Common\Persistence\Mapping\Driver\MappingDriver;
use Doctrine\ORM\Configuration;
use Doctrine\Common\Cache\ArrayCache;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Tools\SchemaTool;

abstract class BaseOrmTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var EntityManager
     */
    protected $em;

    public static function setUpBeforeClass()
    {
        if (false == class_exists('Doctrine\ORM\Version', $autoload = true)) {
            throw new \PHPUnit_Framework_SkippedTestError('Doctrine ORM lib not installed. Have you run composer with --dev option?');
        }
        if (false == extension_loaded('pdo_sqlite')) {
            throw new \PHPUnit_Framework_SkippedTestError('The pdo_sqlite extension is not loaded. It is required to run doctrine tests.');
        }
    }

    protected function setUp()
    {
        $this->setUpEntityManager();
        $this->setUpDatabase();
    }

    protected function setUpEntityManager()
    {
        $config = new Configuration();
        $config->setAutoGenerateProxyClasses(true);
        $config->setProxyDir(\sys_get_temp_dir());
        $config->setProxyNamespace('Proxies');
        $config->setMetadataDriverImpl($this->getMetadataDriverImpl($config));
        $config->setQueryCacheImpl(new ArrayCache());
        $config->setMetadataCacheImpl(new ArrayCache());

        $connection = array('driver' => 'pdo_sqlite', 'path' => ':memory:');

        $this->em = EntityManager::create($connection, $config);
    }

    protected function setUpDatabase()
    {
        $schemaTool = new SchemaTool($this->em);
        $schemaTool->dropDatabase();
        $schemaTool->createSchema($this->em->getMetadataFactory()->getAllMetadata());
    }

    /**
     * @param Configuration $config
     *
     * @return MappingDriver
     */
    abstract protected function getMetadataDriverImpl(Configuration $config);
}
