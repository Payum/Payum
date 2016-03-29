<?php
namespace Payum\Core\Tests\Model;

use Payum\Core\Model\Identificator;

class IdentificatorTest extends \PHPUnit_Framework_TestCase
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
    public function couldBeConstructedWithIdAndModelClassAsArguments()
    {
        new Identificator('anId', 'aClass');
    }

    /**
     * @test
     */
    public function couldBeConstructedWithIdAndModelAsArguments()
    {
        new Identificator('anId', new \stdClass());
    }

    /**
     * @test
     */
    public function shouldAllowGetIdSetInConstructor()
    {
        $id = new Identificator('theId', new \stdClass());

        $this->assertEquals('theId', $id->getId());
    }

    /**
     * @test
     */
    public function shouldAllowGetClassSetInConstructor()
    {
        $id = new Identificator('theId', new \stdClass());

        $this->assertEquals('stdClass', $id->getClass());
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
