<?php
namespace Payum\Payex\Tests\Functional\Bridge\Doctrine;

use Doctrine\Common\Persistence\Mapping\Driver\MappingDriverChain;
use Doctrine\Common\Annotations\AnnotationRegistry;
use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\ORM\Mapping\Driver\SimplifiedXmlDriver;
use Doctrine\ORM\Mapping\Driver\AnnotationDriver;

use Payum\Tests\Functional\Bridge\Doctrine\BaseOrmTest;

abstract class OrmTest extends BaseOrmTest
{
    /**
     * @throws \RuntimeException if cannot guess lib root dir.
     * 
     * @return MappingDriverChain
     */
    protected function getMetadataDriverImpl()
    {
        $rootDir = realpath(__DIR__.'/../../../../../../../');
        if (false === $rootDir || false === is_dir($rootDir.'/src/Payum')) {
            throw new \RuntimeException('Cannot guess Payum root dir.');
        }

        $driver = new MappingDriverChain;

        $xmlDriver = new SimplifiedXmlDriver(array(
            $rootDir.'/src/Payum/Payex/Bridge/Doctrine/Resources/mapping' => 'Payum\Payex\Bridge\Doctrine\Entity'
        ));
        $driver->addDriver($xmlDriver, 'Payum\Payex\Bridge\Doctrine\Entity');

        $rc = new \ReflectionClass('\Doctrine\ORM\Mapping\Driver\AnnotationDriver');
        AnnotationRegistry::registerFile(dirname($rc->getFileName()) . '/DoctrineAnnotations.php');

        $annotationDriver = new AnnotationDriver(new AnnotationReader(), array(
            $rootDir.'/examples/Payum/Payex/Examples/Entity'
        ));
        $driver->addDriver($annotationDriver, 'Payum\Payex\Examples\Entity');

        return $driver;
    }
}