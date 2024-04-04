<?php
namespace Payum\Core\Tests;

use Payum\Core\ApiAwareTrait;
use Payum\Core\Exception\LogicException;
use Payum\Core\Exception\UnsupportedApiException;
use PHPUnit\Framework\TestCase;

class ApiAwareTraitTest extends TestCase
{
    public function testThrowIfSetApiButApiClassNotConfigured()
    {
        $object = new ApiAwareClass;
        $object->setApiClass(null);

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('You must configure apiClass in __constructor method of the class the trait is applied to.');
        $object->setApi(new \stdClass());
    }

    public function testThrowIfSetApiButApiClassIsNotValidClass()
    {
        $object = new ApiAwareClass;
        $object->setApiClass('invalidClass');

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('Api class not found or invalid class. "invalidClass"');
        $object->setApi(new \stdClass());
    }

    public function testThrowUnsupportedApi()
    {
        $object = new ApiAwareClass;
        $object->setApiClass($this->createMock(\stdClass::class));

        $this->expectException(UnsupportedApiException::class);
        $this->expectExceptionMessage('It must be an instance of Mock_stdClass');
        $object->setApi(new \stdClass);
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
