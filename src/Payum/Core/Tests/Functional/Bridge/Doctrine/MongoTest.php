<?php
namespace Payum\Tests\Functional\Bridge\Doctrine;

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
        $rootDir = realpath(__DIR__.'/../../../../../../');
        if (false === $rootDir || false === is_dir($rootDir.'/src/Payum')) {
            throw new \RuntimeException('Cannot guess Payum root dir.');
        }

        $driver = new MappingDriverChain;
        $xmlDriver = new XmlDriver(
            new SymfonyFileLocator(
                array($rootDir.'/src/Payum/Bridge/Doctrine/Resources/mapping' => 'Payum\Core\Model'),
                '.mongodb.xml'
            ),
            '.mongodb.xml'
        );
        $driver->addDriver($xmlDriver, 'Payum\Core\Model');

        \Doctrine\ODM\MongoDB\Mapping\Driver\AnnotationDriver::registerAnnotationClasses();

        $annotationDriver = new AnnotationDriver(new AnnotationReader(), array(
            $rootDir.'/examples/Payum/Examples/Document'
        ));
        $driver->addDriver($annotationDriver, 'Payum\Examples\Document');

        return $driver;
    }
}