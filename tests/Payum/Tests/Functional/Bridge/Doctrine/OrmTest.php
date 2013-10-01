<?php
namespace Payum\Tests\Functional\Bridge\Doctrine;

use Doctrine\Common\Persistence\Mapping\Driver\MappingDriver;
use Doctrine\ORM\Tools\SchemaValidator;
use Doctrine\ORM\Mapping\Driver\SimplifiedXmlDriver;
use Doctrine\Common\Annotations\AnnotationRegistry;
use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Persistence\Mapping\Driver\MappingDriverChain;
use Doctrine\ORM\Mapping\Driver\AnnotationDriver;

class OrmTest extends BaseOrmTest
{
    /**
     * @return MappingDriver
     */
    protected function getMetadataDriverImpl()
    {   
        $rootDir = realpath(__DIR__.'/../../../../../../');
        if (false === $rootDir || false === is_dir($rootDir.'/src/Payum')) {
            throw new \RuntimeException('Cannot guess Payum root dir.');
        }
        
        $driver = new MappingDriverChain;
        
        $xmlDriver = new SimplifiedXmlDriver(array(
            $rootDir.'/src/Payum/Bridge/Doctrine/Resources/mapping' => 'Payum\Bridge\Doctrine\Entity'
        ));
        $driver->addDriver($xmlDriver, 'Payum\Bridge\Doctrine\Entity');

        $rc = new \ReflectionClass('\Doctrine\ORM\Mapping\Driver\AnnotationDriver');
        AnnotationRegistry::registerFile(dirname($rc->getFileName()) . '/DoctrineAnnotations.php');

        $annotationDriver = new AnnotationDriver(new AnnotationReader(), array(
            $rootDir.'/examples/Payum/Examples/Entity'
        ));
        $driver->addDriver($annotationDriver, 'Payum\Examples\Entity');
        
        return $driver;
    }

    /**
     * @test
     */
    public function shouldAllSchemasBeValid()
    {
        $schemaValidator = new SchemaValidator($this->em);

        $this->assertEmpty($schemaValidator->validateMapping());
    }
}