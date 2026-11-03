<?php

declare(strict_types=1);

namespace Payum\Core\Tests\Functional\Bridge\Doctrine;

use Composer\InstalledVersions;
use Doctrine\DBAL\DriverManager;
use Doctrine\ORM\Configuration;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Tools\SchemaTool;
use Doctrine\Persistence\Mapping\Driver\MappingDriver;
use Payum\Core\Bridge\Doctrine\Types\DbalObjectType;
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
        if (! class_exists(EntityManager::class, $autoload = true)) {
            throw new SkippedTestError('Doctrine ORM lib not installed. Have you run composer with --dev option?');
        }
        if (! extension_loaded('pdo_sqlite')) {
            throw new SkippedTestError('The pdo_sqlite extension is not loaded. It is required to run doctrine tests.');
        }

        DbalObjectType::register();
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

        // symfony/var-exporter 8 dropped the lazy ghosts ORM 3 falls back to without native lazy objects.
        if (\PHP_VERSION_ID >= 80400 && version_compare((string) InstalledVersions::getVersion('doctrine/orm'), '3.4', '>=')) {
            $config->enableNativeLazyObjects(true);
        }

        $config->setQueryCache(new ArrayAdapter());
        $config->setMetadataCache(new ArrayAdapter());

        $connection = DriverManager::getConnection([
            'driver' => 'pdo_sqlite',
            'memory' => true,
        ], $config);

        $this->em = new EntityManager($connection, $config);
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
