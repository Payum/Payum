<?php

namespace Payum\Core\Tests\Model;

use Payum\Core\Model\Identity;
use Payum\Core\Storage\IdentityInterface;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use stdClass;

class IdentityTest extends TestCase
{
    public function testShouldImplementIdentityInterface(): void
    {
        $rc = new ReflectionClass(Identity::class);

        $this->assertTrue($rc->implementsInterface(IdentityInterface::class));
    }

    public function testShouldAllowGetIdSetInConstructor(): void
    {
        $id = new Identity('theId', new stdClass());

        $this->assertSame('theId', $id->getId());
    }

    public function testShouldAllowGetClassSetInConstructor(): void
    {
        $id = new Identity('theId', new stdClass());

        $this->assertSame(stdClass::class, $id->getClass());
    }

    public function testShouldBeCorrectlySerializedAndUnserialized(): void
    {
        $id = new Identity('theId', new stdClass());

        $serializedId = serialize($id);

        $unserializedId = unserialize($serializedId);

        $this->assertEquals($id, $unserializedId);
    }
}
