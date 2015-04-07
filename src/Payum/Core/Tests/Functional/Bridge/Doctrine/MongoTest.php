<?php
namespace Payum\Core\Tests\Functional\Bridge\Doctrine;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Persistence\Mapping\Driver\MappingDriverChain;
use Doctrine\Common\Persistence\Mapping\Driver\SymfonyFileLocator;
use Doctrine\ODM\MongoDB\Mapping\Driver\AnnotationDriver;
use Doctrine\ODM\MongoDB\Mapping\Driver\XmlDriver;

abstract class MongoTest extends BaseMongoTest
{
    /**
     * @throws \RuntimeException
     *
     * @return MappingDriverChain
     */
    protected function getMetadataDriverImpl()
    {
        $rootDir = realpath(__DIR__.'/../../../..');
        if (false === $rootDir || false === is_file($rootDir.'/Gateway.php')) {
            throw new \RuntimeException('Cannot guess Payum root dir.');
        }

        $driver = new MappingDriverChain();
        $xmlDriver = new XmlDriver(
            new SymfonyFileLocator(
                array($rootDir.'/Bridge/Doctrine/Resources/mapping' => 'Payum\Core\Model'),
                '.mongodb.xml'
            ),
            '.mongodb.xml'
        );
        $driver->addDriver($xmlDriver, 'Payum\Core\Model');

        AnnotationDriver::registerAnnotationClasses();

        $rc = new \ReflectionClass('Payum\Core\Tests\Mocks\Document\TestModel');
        $annotationDriver = new AnnotationDriver(new AnnotationReader(), array(
            dirname($rc->getFileName()),
        ));
        $driver->addDriver($annotationDriver, 'Payum\Core\Tests\Mocks\Document');

        return $driver;
    }
}
