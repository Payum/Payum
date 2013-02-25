<?php
namespace Payum\Paypal\ExpressCheckout\Nvp\Tests\Functional\Bridge\Doctrine;

use Doctrine\Common\Persistence\Mapping\Driver\MappingDriverChain;
use Doctrine\ORM\Mapping\Driver\SimplifiedXmlDriver;
use Doctrine\Common\Annotations\AnnotationRegistry;
use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\ORM\Mapping\Driver\AnnotationDriver;

use Payum\Tests\Functional\Bridge\Doctrine\BaseOrmTest;

abstract class OrmTest extends BaseOrmTest
{
    /**
     * @return array
     */
    protected function getMetadataDriverImpl()
    {   
        $rootDir = realpath(__DIR__.'/../../../../../../../../../');
        if (false === $rootDir || false === is_dir($rootDir.'/src/Payum')) {
            throw new \RuntimeException('Cannot guess Payum root dir.');
        }

        $driver = new MappingDriverChain;
        
        $xmlDriver = new SimplifiedXmlDriver(array(
            $rootDir.'/src/Payum/Paypal/ExpressCheckout/Nvp/Bridge/Doctrine/Resources/mapping' => 'Payum\Paypal\ExpressCheckout\Nvp\Bridge\Doctrine\Entity'
        ));        
        $driver->addDriver($xmlDriver, 'Payum\Paypal\ExpressCheckout\Nvp\Bridge\Doctrine\Entity');

        $rc = new \ReflectionClass('\Doctrine\ORM\Mapping\Driver\AnnotationDriver');
        AnnotationRegistry::registerFile(dirname($rc->getFileName()) . '/DoctrineAnnotations.php');

        $annotationDriver = new AnnotationDriver(new AnnotationReader(), array(
            $rootDir.'/examples/Payum/Paypal/ExpressCheckout/Nvp/Examples/Entity'
        ));
        $driver->addDriver($annotationDriver, 'Payum\Paypal\ExpressCheckout\Nvp\Examples\Entity');
        
        return $driver;
    }
}