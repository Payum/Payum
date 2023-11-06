<?php
namespace Payum\Paypal\ExpressCheckout\Nvp\Tests\Action\Api;

class BaseApiAwareActionTest extends \PHPUnit\Framework\TestCase
{
    public function testShouldImplementActionInterface()
    {
        $rc = new \ReflectionClass(\Payum\Paypal\ExpressCheckout\Nvp\Action\Api\BaseApiAwareAction::class);

        $this->assertTrue($rc->isSubclassOf(\Payum\Core\Action\ActionInterface::class));
    }

    public function testShouldImplementApiAwareInterface()
    {
        $rc = new \ReflectionClass(\Payum\Paypal\ExpressCheckout\Nvp\Action\Api\BaseApiAwareAction::class);

        $this->assertTrue($rc->isSubclassOf(\Payum\Core\ApiAwareInterface::class));
    }

    public function testShouldBeAbstract()
    {
        $rc = new \ReflectionClass(\Payum\Paypal\ExpressCheckout\Nvp\Action\Api\BaseApiAwareAction::class);

        $this->assertTrue($rc->isAbstract());
    }

    public function testThrowIfUnsupportedApiGiven()
    {
        $this->expectException(\Payum\Core\Exception\UnsupportedApiException::class);
        $action = $this->getMockForAbstractClass(\Payum\Paypal\ExpressCheckout\Nvp\Action\Api\BaseApiAwareAction::class);

        $action->setApi(new \stdClass());
    }

    /**
     * @return \PHPUnit\Framework\MockObject\MockObject|\Payum\Paypal\ExpressCheckout\Nvp\Api
     */
    protected function createApiMock()
    {
        return $this->createMock(\Payum\Paypal\ExpressCheckout\Nvp\Api::class);
    }
}
