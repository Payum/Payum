<?php
namespace Payum\Be2Bill\Tests\Functional\Bridge\Doctrine;

use Doctrine\Common\Persistence\Mapping\Driver\MappingDriverChain;
use Doctrine\ORM\Mapping\Driver\SimplifiedXmlDriver;
use Doctrine\Common\Annotations\AnnotationRegistry;
use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\ORM\Mapping\Driver\AnnotationDriver;
use Payum\Tests\Functional\Bridge\Doctrine\BaseOrmTest;

abstract class OrmTest extends BaseOrmTest
{
    /**
     * {@inheritDoc}
     */
    protected function getMetadataDriverImpl()
    {   
        $rootDir = realpath(__DIR__.'/../../../../../../../');
        if (false === $rootDir || false === is_dir($rootDir.'/src/Payum')) {
            throw new \RuntimeException('Cannot guess Payum root dir.');
        }

        $driver = new MappingDriverChain;
        
        $xmlDriver = new SimplifiedXmlDriver(array(
            $rootDir.'/src/Payum/Be2Bill/Bridge/Doctrine/Resources/mapping' => 'Payum\Be2Bill\Model'
        ));        
        $driver->addDriver($xmlDriver, 'Payum\Be2Bill\Model');

        $rc = new \ReflectionClass('\Doctrine\ORM\Mapping\Driver\AnnotationDriver');
        AnnotationRegistry::registerFile(dirname($rc->getFileName()) . '/DoctrineAnnotations.php');

        $annotationDriver = new AnnotationDriver(new AnnotationReader(), array(
            $rootDir.'/examples/Payum/Be2Bill/Examples/Entity'
        ));
        $driver->addDriver($annotationDriver, 'Payum\Be2Bill\Examples\Entity');
        
        return $driver;
    }
}