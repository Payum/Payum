<?php
namespace Payum\Core\Tests;

use PHPUnit\Framework\TestCase as PHPUnitTestCase;

abstract class TestCase extends PHPUnitTestCase
{
    /**
     * @deprecated Do not test internal properties. The object should be re-factored to allow better testing
     */
    public static function readAttribute($object, string $attributeName)
    {
        $attribute = new \ReflectionProperty($object, $attributeName);
        $attribute->setAccessible(true);

        return $attribute->getValue($object);
    }
}
