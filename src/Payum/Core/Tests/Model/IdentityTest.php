<?php
namespace Payum\Core\Tests\Model;

use Payum\Core\Model\Identity;

class IdentityTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldImplementIdentityInterface()
    {
        $rc = new \ReflectionClass('Payum\Core\Model\Identity');

        $this->assertTrue($rc->implementsInterface('Payum\Core\Storage\IdentityInterface'));
    }

    /**
     * @test
     */
    public function couldBeConstructedWithIdAndModelClassAsArguments()
    {
        new Identity('anId', 'aClass');
    }

    /**
     * @test
     */
    public function couldBeConstructedWithIdAndModelAsArguments()
    {
        new Identity('anId', new \stdClass());
    }

    /**
     * @test
     */
    public function shouldAllowGetIdSetInConstructor()
    {
        $id = new Identity('theId', new \stdClass());

        $this->assertEquals('theId', $id->getId());
    }

    /**
     * @test
     */
    public function shouldAllowGetClassSetInConstructor()
    {
        $id = new Identity('theId', new \stdClass());

        $this->assertEquals('stdClass', $id->getClass());
    }

    /**
     * @test
     */
    public function shouldBeCorrectlySerializedAndUnserialized()
    {
        $id = new Identity('theId', new \stdClass());

        $serializedId = serialize($id);

        $unserializedId = unserialize($serializedId);

        $this->assertEquals($id, $unserializedId);
    }
}
