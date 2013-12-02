<?php
namespace Payum\Core\Tests\Request;

use Payum\Core\Request\NotifyRequest;

class NotifyRequestTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldBeSubClassOfBaseModelRequest()
    {
        $rc = new \ReflectionClass('Payum\Core\Request\NotifyRequest');

        $this->assertTrue($rc->isSubclassOf('Payum\Core\Request\BaseModelRequest'));
    }

    /**
     * @test
     */
    public function couldBeConstructedWithNotificationAsFirstArgument()
    {
        new NotifyRequest(array(
            'foo' => 'aFoo'
        ));
    }

    /**
     * @test
     */
    public function shouldAllowGetNotificationSetInConstructor()
    {
        $expectedNotification = array(
            'foo' => 'aFooValue',
            'bar' => 'aBarValue'
        );
        
        $request = new NotifyRequest($expectedNotification);
        
        $this->assertSame($expectedNotification, $request->getNotification());
    }

    /**
     * @test
     */
    public function shouldAllowGetModelSetInConstructor()
    {
        $expectedModel = new \stdClass;

        $request = new NotifyRequest(array(), $expectedModel);

        $this->assertSame($expectedModel, $request->getModel());
    }
}