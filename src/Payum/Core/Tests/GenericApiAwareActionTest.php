<?php

namespace Payum\Core\Tests;

use Payum\Core\ApiAwareInterface;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ReflectionProperty;

abstract class GenericApiAwareActionTest extends TestCase
{
    public function testShouldImplementApiAwareInterface(): void
    {
        $rc = new ReflectionClass($this->getActionClass());

        $this->assertTrue($rc->implementsInterface(ApiAwareInterface::class));
    }

    public function testShouldAllowSetApi(): void
    {
        $apiClass = $this->getApiClass();
        $class = $this->getActionClass();
        $api = new $class();
        $api->setApi($apiClass);

        $rc = new ReflectionProperty($api, 'apiClass');
        $rc->setAccessible(true);

        $this->assertInstanceOf($rc->getValue($api), $apiClass);
    }

    abstract protected function getApiClass();

    abstract protected function getActionClass(): string;
}
