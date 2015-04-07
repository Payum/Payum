<?php
namespace Payum\Bundle\PayumBundle\Sonata\Tests;

use Payum\Bundle\PayumBundle\Sonata\GatewayConfigAdmin;

class GatewayConfigAdminTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldBeSubClassSonataAdmin()
    {
        $rc = new \ReflectionClass('Payum\Bundle\PayumBundle\Sonata\GatewayConfigAdmin');

        $this->assertTrue($rc->isSubclassOf('Sonata\AdminBundle\Admin\Admin'));
    }

    /**
     * @test
     */
    public function couldBeConstructedWithExpectedArguments()
    {
        new GatewayConfigAdmin('code', 'class', 'baseControllerName');
    }

    /**
     * @test
     */
    public function shouldAllowSetFormFactory()
    {
        $admin = new GatewayConfigAdmin('code', 'class', 'baseControllerName');

        $formFactoryMock = $this->getMock('Symfony\Component\Form\FormFactoryInterface');

        $admin->setFormFactory($formFactoryMock);

        $this->assertAttributeSame($formFactoryMock, 'formFactory', $admin);
    }
}

