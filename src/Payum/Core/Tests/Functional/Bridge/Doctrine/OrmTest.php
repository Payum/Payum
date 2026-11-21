<?php

declare(strict_types=1);

namespace Payum\Core\Tests\Functional\Bridge\Doctrine;

use Doctrine\ORM\Configuration;
use Doctrine\ORM\Mapping\Driver\AttributeDriver;
use Doctrine\ORM\Mapping\Driver\SimplifiedXmlDriver;
use Doctrine\ORM\Tools\ResolveTargetEntityListener;
use Doctrine\Persistence\Mapping\Driver\MappingDriver;
use Doctrine\Persistence\Mapping\Driver\MappingDriverChain;
use Payum\Core\Bridge\Doctrine\ResolveTargets;
use Payum\Core\Tests\Mocks\Entity\TestModel;
use ReflectionClass;
use RuntimeException;

abstract class OrmTest extends BaseOrmTest
{
    protected function setUpEntityManager(): void
    {
        parent::setUpEntityManager();

        $listener = new ResolveTargetEntityListener();
        foreach (ResolveTargets::DEFAULTS as $interface => $model) {
            $listener->addResolveTargetEntity($interface, 'Payum\\Core\\Tests\\Mocks\\Entity\\' . (new ReflectionClass($model))->getShortName(), []);
        }

        $this->em->getEventManager()->addEventSubscriber($listener);
    }

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
        $driver->addDriver(new AttributeDriver([dirname($rc->getFileName())], true), 'Payum\Core\Tests\Mocks\Entity');

        return $driver;
    }
}
