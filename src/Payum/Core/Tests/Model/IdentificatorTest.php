<?php

namespace Payum\Core\Tests\Model;

use Payum\Core\Model\Identificator;
use PHPUnit\Framework\TestCase;

class IdentificatorTest extends TestCase
{
    /**
     * @test
     */
    public function shouldImplementSerializableInterface()
    {
        $rc = new \ReflectionClass(Identificator::class);

        $this->assertTrue($rc->implementsInterface(\Serializable::class));
    }

    /**
     * @test
     */
    public function shouldAllowGetIdSetInConstructor()
    {
        $id = new Identificator('theId', new \stdClass());

        $this->assertSame('theId', $id->getId());
    }

    /**
     * @test
     */
    public function shouldAllowGetClassSetInConstructor()
    {
        $id = new Identificator('theId', new \stdClass());

        $this->assertSame('stdClass', $id->getClass());
    }

    /**
     * @test
     */
    public function shouldBeCorrectlySerializedAndUnserialized()
    {
        $id = new Identificator('theId', new \stdClass());

        $serializedId = serialize($id);

        $unserializedId = unserialize($serializedId);

        $this->assertEquals($id, $unserializedId);
    }
}
