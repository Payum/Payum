<?php
namespace Payum\Core\Tests\Model;

use Payum\Core\Model\Identificator;
use PHPUnit\Framework\TestCase;

class IdentificatorTest extends TestCase
{
    public function testShouldImplementSerializableInterface()
    {
        $rc = new \ReflectionClass(Identificator::class);

        $this->assertTrue($rc->implementsInterface(\Serializable::class));
    }

    public function testShouldAllowGetIdSetInConstructor()
    {
        $id = new Identificator('theId', new \stdClass());

        $this->assertSame('theId', $id->getId());
    }

    public function testShouldAllowGetClassSetInConstructor()
    {
        $id = new Identificator('theId', new \stdClass());

        $this->assertSame('stdClass', $id->getClass());
    }

    public function testShouldBeCorrectlySerializedAndUnserialized()
    {
        $id = new Identificator('theId', new \stdClass());

        $serializedId = serialize($id);

        $unserializedId = unserialize($serializedId);

        $this->assertEquals($id, $unserializedId);
    }
}
