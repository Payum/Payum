<?php
namespace Payum\Core\Tests\Model;

use Payum\Core\Model\Identity;
use Payum\Core\Storage\IdentityInterface;
use PHPUnit\Framework\TestCase;

class IdentityTest extends TestCase
{
    public function testShouldImplementIdentityInterface()
    {
        $rc = new \ReflectionClass(Identity::class);

        $this->assertTrue($rc->implementsInterface(IdentityInterface::class));
    }

    public function testShouldAllowGetIdSetInConstructor()
    {
        $id = new Identity('theId', new \stdClass());

        $this->assertSame('theId', $id->getId());
    }

    public function testShouldAllowGetClassSetInConstructor()
    {
        $id = new Identity('theId', new \stdClass());

        $this->assertSame('stdClass', $id->getClass());
    }

    public function testShouldBeCorrectlySerializedAndUnserialized()
    {
        $id = new Identity('theId', new \stdClass());

        $serializedId = serialize($id);

        $unserializedId = unserialize($serializedId);

        $this->assertEquals($id, $unserializedId);
    }
}
