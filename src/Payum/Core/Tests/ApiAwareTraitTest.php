<?php
namespace Payum\Core\Tests;

use Payum\Core\ApiAwareTrait;
use Payum\Core\Exception\LogicException;
use Payum\Core\Exception\UnsupportedApiException;

class ApiAwareTraitTest extends \PHPUnit_Framework_TestCase
{
    public function testThrowIfSetApiButApiClassNotConfigured()
    {
        $object = new ApiAwareClass;
        $object->setApiClass(null);

        $this->setExpectedException(LogicException::class, 'You must configure apiClass in __constructor method of the class the trait is applied to.');
        $object->setApi(new \stdClass());
    }

    public function testThrowIfSetApiButApiClassIsNotValidClass()
    {
        $object = new ApiAwareClass;
        $object->setApiClass('invalidClass');

        $this->setExpectedException(LogicException::class, 'Api class not found or invalid class. "invalidClass"');
        $object->setApi(new \stdClass());
    }

    public function testThrowUnsupportedApi()
    {
        $object = new ApiAwareClass;
        $object->setApiClass($this->getMockClass(\stdClass::class));

        $this->setExpectedException(UnsupportedApiException::class, 'It must be an instance of Mock_stdClass');
        $object->setApi(new \stdClass);
    }

    public function testShouldSetApiIfSupported()
    {
        $expectedApi = new \stdClass;

        $object = new ApiAwareClass;
        $object->setApiClass(\stdClass::class);

        $object->setApi($expectedApi);

        $this->assertAttributeSame($expectedApi, 'api', $object);
    }

    public function testShouldSetApiIfSupportedWithInterface()
    {
        $expectedApi = new FooApi;

        $object = new ApiAwareClass;
        $object->setApiClass(FooInterface::class);

        $object->setApi($expectedApi);

        $this->assertAttributeSame($expectedApi, 'api', $object);
    }
}

class ApiAwareClass
{
    public function setApiClass($apiClass)
    {
        $this->apiClass = $apiClass;
    }


    use ApiAwareTrait;
}

interface FooInterface
{
}

class FooApi implements FooInterface
{
}
