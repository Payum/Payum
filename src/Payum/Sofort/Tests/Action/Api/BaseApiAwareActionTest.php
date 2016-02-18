<?php

namespace Payum\Sofort\Tests\Action\Api;

use Payum\Core\Action\ActionInterface;
use Payum\Core\ApiAwareInterface;
use Payum\Sofort\Api;
use Payum\Sofort\Action\Api\BaseApiAwareAction;

class BaseApiAwareActionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldImplementActionInterface()
    {
        $rc = new \ReflectionClass(BaseApiAwareAction::class);

        $this->assertTrue($rc->isSubclassOf(ActionInterface::class));
    }

    /**
     * @test
     */
    public function shouldImplementApiAwareInterface()
    {
        $rc = new \ReflectionClass(BaseApiAwareAction::class);

        $this->assertTrue($rc->isSubclassOf(ApiAwareInterface::class));
    }

    /**
     * @test
     */
    public function shouldBeAbstract()
    {
        $rc = new \ReflectionClass(BaseApiAwareAction::class);

        $this->assertTrue($rc->isAbstract());
    }

    /**
     * @test
     */
    public function shouldAllowSetApi()
    {
        $expectedApi = $this->createApiMock();

        $action = $this->getMockForAbstractClass(BaseApiAwareAction::class);

        $action->setApi($expectedApi);

        $this->assertAttributeSame($expectedApi, 'api', $action);
    }

    /**
     * @test
     *
     * @expectedException \Payum\Core\Exception\UnsupportedApiException
     */
    public function throwIfUnsupportedApiGiven()
    {
        $action = $this->getMockForAbstractClass(BaseApiAwareAction::class);

        $action->setApi(new \stdClass());
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|\Payum\Sofort\Api
     */
    protected function createApiMock()
    {
        return $this->getMock(Api::class, array(), array(), '', false);
    }
}
