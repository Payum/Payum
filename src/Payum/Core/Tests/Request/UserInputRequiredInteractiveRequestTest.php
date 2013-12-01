<?php
namespace Payum\Tests\Request;

use Payum\Core\Request\UserInputRequiredInteractiveRequest;

class UserInputRequiredInteractiveRequestTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldImplementInteractiveRequestInterface()
    {
        $rc = new \ReflectionClass('Payum\Core\Request\UserInputRequiredInteractiveRequest');

        $this->assertTrue($rc->implementsInterface('Payum\Core\Request\InteractiveRequestInterface'));
    }

    /**
     * @test
     */
    public function couldBeConstructedWithModelAndRequiredFieldsAsArgument()
    {
        new UserInputRequiredInteractiveRequest(array(
            'a_field',
        ));
    }

    /**
     * @test
     */
    public function shouldAllowGetRequiredFieldsSetInConstructor()
    {
        $expectedRequiredFields = array(
            'a_field',
        );

        $request = new \Payum\Core\Request\UserInputRequiredInteractiveRequest($expectedRequiredFields);

        $this->assertEquals($expectedRequiredFields, $request->getRequiredFields());
    }
}