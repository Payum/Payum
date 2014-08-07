<?php
namespace Payum\Core\Tests\Functional\Bridge\Doctrine;

use Doctrine\Common\Persistence\Mapping\Driver\MappingDriver;
use Doctrine\ORM\Mapping\Driver\DriverChain;
use Doctrine\ORM\Mapping\Driver\SimplifiedXmlDriver;
use Doctrine\Common\Annotations\AnnotationRegistry;
use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\ORM\Mapping\Driver\AnnotationDriver;

abstract class OrmTest extends BaseOrmTest
{
    /**
     * @return MappingDriver
     */
    protected function getMetadataDriverImpl()
    {   
        $rootDir = realpath(__DIR__.'/../../../..');
        if (false === $rootDir || false === is_file($rootDir.'/Payment.php')) {
            throw new \RuntimeException('Cannot guess Payum root dir.');
        }
        
        $driver = new DriverChain;
        
        $xmlDriver = new SimplifiedXmlDriver(array(
            $rootDir.'/Bridge/Doctrine/Resources/mapping' => 'Payum\Core\Model'
        ));
        $driver->addDriver($xmlDriver, 'Payum\Core\Model');

        $rc = new \ReflectionClass('\Doctrine\ORM\Mapping\Driver\AnnotationDriver');
        AnnotationRegistry::registerFile(dirname($rc->getFileName()) . '/DoctrineAnnotations.php');

        $rc = new \ReflectionClass('Payum\Core\Tests\Mocks\Entity\TestModel');
        $annotationDriver = new AnnotationDriver(new AnnotationReader(), array(
            dirname($rc->getFileName())
        ));
        $driver->addDriver($annotationDriver, 'Payum\Core\Tests\Mocks\Entity');
        
        return $driver;
    }
}