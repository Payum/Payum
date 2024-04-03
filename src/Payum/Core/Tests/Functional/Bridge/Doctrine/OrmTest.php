<?php

namespace Payum\Core\Tests\Functional\Bridge\Doctrine;

use Doctrine\ORM\Configuration;
use Doctrine\ORM\Mapping\Driver\SimplifiedXmlDriver;
use Doctrine\Persistence\Mapping\Driver\MappingDriver;
use Doctrine\Persistence\Mapping\Driver\MappingDriverChain;
use Payum\Core\Tests\Mocks\Entity\TestModel;
use ReflectionClass;
use RuntimeException;

abstract class OrmTest extends BaseOrmTest
{
    /**
     * @return MappingDriver
     */
    protected function getMetadataDriverImpl(Configuration $config)
    {
        $rootDir = realpath(__DIR__ . '/../../../..');
        if (false === $rootDir || false === is_file($rootDir . '/Gateway.php')) {
            throw new RuntimeException('Cannot guess Payum root dir.');
        }

        $driver = new MappingDriverChain();

        $xmlDriver = new SimplifiedXmlDriver([
            $rootDir . '/Bridge/Doctrine/Resources/mapping' => 'Payum\Core\Model',
        ]);
        $driver->addDriver($xmlDriver, 'Payum\Core\Model');

        $rc = new ReflectionClass(TestModel::class);
        $annotationDriver = $config->newDefaultAnnotationDriver([
            dirname($rc->getFileName()),
        ], false);

        $driver->addDriver($annotationDriver, 'Payum\Core\Tests\Mocks\Entity');

        return $driver;
    }
}
