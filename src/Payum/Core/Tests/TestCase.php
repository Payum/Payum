<?php

declare(strict_types=1);

namespace Payum\Core\Tests;

use PHPUnit\Framework\TestCase as PHPUnitTestCase;
use ReflectionProperty;

abstract class TestCase extends PHPUnitTestCase
{
    /**
     * @deprecated Do not test internal properties. The object should be re-factored to allow better testing
     */
    public static function readAttribute($object, string $attributeName)
    {
        $attribute = new ReflectionProperty($object, $attributeName);

        return $attribute->getValue($object);
    }
}
