<?php
namespace Payum\Core\Tests;

use Payum\Core\ApiAwareInterface;
use PHPUnit\Framework\TestCase;

abstract class GenericApiAwareActionTest extends TestCase
{
    abstract protected function getApiClass();
    abstract protected function getActionClass(): string;

    public function testShouldImplementApiAwareInterface()
    {
        $rc = new \ReflectionClass($this->getActionClass());

        $this->assertTrue($rc->implementsInterface(ApiAwareInterface::class));
    }

    public function testShouldAllowSetApi()
    {
        $apiClass = $this->getApiClass();
        $class = $this->getActionClass();
        $api = new $class();
        $api->setApi($apiClass);

        $rc = new \ReflectionProperty($api, 'apiClass');
        $rc->setAccessible(true);

        $this->assertInstanceOf($rc->getValue($api), $apiClass);
    }
}
