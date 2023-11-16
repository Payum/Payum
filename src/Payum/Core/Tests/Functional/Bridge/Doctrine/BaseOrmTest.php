<?php

namespace Payum\Core\Tests\Functional\Bridge\Doctrine;

use Doctrine\Common\Cache\ArrayCache;
use Doctrine\ORM\Configuration;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Tools\SchemaTool;
use Doctrine\ORM\Version;
use Doctrine\Persistence\Mapping\Driver\MappingDriver;
use PHPUnit\Framework\SkippedTestError;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Cache\Adapter\ArrayAdapter;

abstract class BaseOrmTest extends TestCase
{
    /**
     * @var EntityManager
     */
    protected $em;

    public static function setUpBeforeClass(): void
    {
        if (false == class_exists(Version::class, $autoload = true)) {
            throw new SkippedTestError('Doctrine ORM lib not installed. Have you run composer with --dev option?');
        }
        if (false == extension_loaded('pdo_sqlite')) {
            throw new SkippedTestError('The pdo_sqlite extension is not loaded. It is required to run doctrine tests.');
        }
    }

    protected function setUp(): void
    {
        $this->setUpEntityManager();
        $this->setUpDatabase();
    }

    protected function setUpEntityManager(): void
    {
        $config = new Configuration();
        $config->setAutoGenerateProxyClasses(true);
        $config->setProxyDir(\sys_get_temp_dir());
        $config->setProxyNamespace('Proxies');
        $config->setMetadataDriverImpl($this->getMetadataDriverImpl($config));

        if (method_exists($config, 'setQueryCache')) {
            $config->setQueryCache(new ArrayAdapter());
            $config->setMetadataCache(new ArrayAdapter());
        } else {
            $config->setQueryCacheImpl(new ArrayCache());
            $config->setMetadataCacheImpl(new ArrayCache());
        }

        $connection = [
            'driver' => 'pdo_sqlite',
            'path' => ':memory:',
        ];

        $this->em = EntityManager::create($connection, $config);
    }

    protected function setUpDatabase(): void
    {
        $schemaTool = new SchemaTool($this->em);
        $schemaTool->dropDatabase();
        $schemaTool->createSchema($this->em->getMetadataFactory()->getAllMetadata());
    }

    /**
     * @return MappingDriver
     */
    abstract protected function getMetadataDriverImpl(Configuration $config);
}
