<?php
namespace Payum\Paypal\ExpressCheckout\Nvp\Tests\Functional\Bridge\Doctrine;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Persistence\Mapping\Driver\MappingDriverChain;
use Doctrine\Common\Persistence\Mapping\Driver\SymfonyFileLocator;
use Doctrine\ODM\MongoDB\Mapping\Driver\AnnotationDriver;
use Doctrine\ODM\MongoDB\Mapping\Driver\XmlDriver;
use Payum\Tests\Functional\Bridge\Doctrine\BaseMongoTest;

abstract class MongoTest extends BaseMongoTest
{
    /**
     * {@inheritDoc}
     */
    protected function getMetadataDriverImpl()
    {
        $rootDir = realpath(__DIR__.'/../../../../../../../../../');
        if (false === $rootDir || false === is_dir($rootDir.'/src/Payum')) {
            throw new \RuntimeException('Cannot guess Payum root dir.');
        }

        $driver = new MappingDriverChain;
        $xmlDriver = new XmlDriver(
            new SymfonyFileLocator(array($rootDir.'/src/Payum/Paypal/ExpressCheckout/Nvp/Bridge/Doctrine/Resources/mapping' => 'Payum\Paypal\ExpressCheckout\Nvp\Bridge\Doctrine\Document'), '.mongodb.xml'),
            '.mongodb.xml'
        );
        $driver->addDriver($xmlDriver, 'Payum\Paypal\ExpressCheckout\Nvp\Bridge\Doctrine\Document');

        \Doctrine\ODM\MongoDB\Mapping\Driver\AnnotationDriver::registerAnnotationClasses();

        $annotationDriver = new AnnotationDriver(new AnnotationReader(), array(
            $rootDir.'/examples/Payum/Paypal/ExpressCheckout/Nvp/ExamplesDocument'
        ));
        $driver->addDriver($annotationDriver, 'Payum\Paypal\ExpressCheckout\Nvp\Examples\Document');

        return $driver;
    }
}