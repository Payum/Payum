<?php
namespace Payum\Stripe\Tests\Extension;

use Payum\Core\Extension\Context;
use Payum\Core\Extension\ExtensionInterface;
use Payum\Core\GatewayInterface;
use Payum\Core\Request\Capture;
use Payum\Core\Request\Generic;
use Payum\Core\Request\Refund;
use Payum\Stripe\Constants;
use Payum\Stripe\Extension\CreateCustomerExtension;
use Payum\Stripe\Request\Api\CreateCustomer;

class CreateCustomerExtensionTest extends \PHPUnit_Framework_TestCase
{
    public function testShouldImplementExtensionInterface()
    {
        $rc = new \ReflectionClass(CreateCustomerExtension::class);

        $this->assertTrue($rc->implementsInterface(ExtensionInterface::class));
    }

    public function testCouldBeConstructedWithoutAnyArguments()
    {
        new CreateCustomerExtension();
    }

    public function testShouldCreateCustomerAndReplaceCardToken()
    {
        $model = new \ArrayObject([
            'card' => 'theCardToken',
            'local' => ['save_card' => true],
        ]);
        $request = new Capture($model);
        
        $gatewayMock = $this->createGatewayMock();
        $gatewayMock
            ->expects($this->once())
            ->method('execute')
            ->with($this->isInstanceOf(CreateCustomer::class))
            ->willReturnCallback(function(CreateCustomer $request) {
                $model = $request->getModel();

                $this->assertInstanceOf(\ArrayObject::class, $model);

                $this->assertEquals(['card' => 'theCardToken'], (array) $model);

                $model['id'] = 'theCustomerId';
            });
        ;

        $context = new Context($gatewayMock, $request, []);

        $extension = new CreateCustomerExtension();
        $extension->onPreExecute($context);

        $this->assertEquals([
            'customer' => 'theCustomerId',
            'local' => [
                'save_card' => true,
                'customer' => [
                    'id' => 'theCustomerId',
                    'card' => 'theCardToken',
                ]
            ],
        ], (array) $request->getModel());
    }

    public function testShouldCreateCustomerWithCustomInfoAndReplaceCardToken()
    {
        $model = new \ArrayObject([
            'card' => 'theCardToken',
            'local' => [
                'save_card' => true,
                'customer' => ['foo' => 'fooVal', 'bar' => 'barVal'],
            ],
        ]);
        $request = new Capture($model);

        $gatewayMock = $this->createGatewayMock();
        $gatewayMock
            ->expects($this->once())
            ->method('execute')
            ->with($this->isInstanceOf(CreateCustomer::class))
            ->willReturnCallback(function(CreateCustomer $request) {
                $model = $request->getModel();

                $this->assertInstanceOf(\ArrayObject::class, $model);

                $this->assertEquals([
                    'card' => 'theCardToken',
                    'foo' => 'fooVal',
                    'bar' => 'barVal'
                ], (array) $model);

                $model['id'] = 'theCustomerId';
            });
        ;

        $context = new Context($gatewayMock, $request, []);

        $extension = new CreateCustomerExtension();
        $extension->onPreExecute($context);

        $this->assertEquals([
            'customer' => 'theCustomerId',
            'local' => [
                'save_card' => true,
                'customer' => [
                    'id' => 'theCustomerId',
                    'card' => 'theCardToken',
                    'foo' => 'fooVal',
                    'bar' => 'barVal'
                ]
            ],
        ], (array) $request->getModel());
    }

    public function testShouldSetStatusFailedIfCreateCustomerRequestFailed()
    {
        $model = new \ArrayObject([
            'card' => 'theCardToken',
            'local' => [
                'save_card' => true,
            ],
        ]);
        $request = new Capture($model);

        $gatewayMock = $this->createGatewayMock();
        $gatewayMock
            ->expects($this->once())
            ->method('execute')
            ->with($this->isInstanceOf(CreateCustomer::class))
            ->willReturnCallback(function(CreateCustomer $request) {
                $model = $request->getModel();

                $this->assertInstanceOf(\ArrayObject::class, $model);

                $this->assertEquals(['card' => 'theCardToken'], (array) $model);

                // we assume the customer creation has failed when the customer does not have an id set.
                $model['id'] = null;
            });
        ;

        $context = new Context($gatewayMock, $request, []);

        $extension = new CreateCustomerExtension();
        $extension->onPreExecute($context);

        $this->assertEquals([
            'status' => Constants::STATUS_FAILED,
            'local' => [
                'save_card' => true,
                'customer' => [
                    'id' => null,
                    'card' => 'theCardToken',
                ]
            ],
        ], (array) $request->getModel());
    }

    public function testShouldDoNothingIfNotCaptureRequest()
    {
        $model = new \ArrayObject([
            'card' => 'theCardToken',
            'local' => [
                'save_card' => true,
            ],
        ]);
        $request = new Refund($model);

        $gatewayMock = $this->createGatewayMock();
        $gatewayMock
            ->expects($this->never())
            ->method('execute')
        ;

        $context = new Context($gatewayMock, $request, []);

        $extension = new CreateCustomerExtension();
        $extension->onPreExecute($context);

        $this->assertEquals([
            'card' => 'theCardToken',
            'local' => [
                'save_card' => true,
            ],
        ], (array) $request->getModel());
    }

    public function testShouldDoNothingIfSaveCardNotSet()
    {
        $model = new \ArrayObject([
            'card' => 'theCardToken',
        ]);
        $request = new Capture($model);

        $gatewayMock = $this->createGatewayMock();
        $gatewayMock
            ->expects($this->never())
            ->method('execute')
        ;

        $context = new Context($gatewayMock, $request, []);

        $extension = new CreateCustomerExtension();
        $extension->onPreExecute($context);

        $this->assertEquals([
            'card' => 'theCardToken',
        ], (array) $request->getModel());
    }

    public function testShouldDoNothingIfSaveCardSetToFalse()
    {
        $model = new \ArrayObject([
            'card' => 'theCardToken',
            'local' => [
                'save_card' => false,
            ],
        ]);
        $request = new Capture($model);

        $gatewayMock = $this->createGatewayMock();
        $gatewayMock
            ->expects($this->never())
            ->method('execute')
        ;

        $context = new Context($gatewayMock, $request, []);

        $extension = new CreateCustomerExtension();
        $extension->onPreExecute($context);

        $this->assertEquals([
            'card' => 'theCardToken',
            'local' => [
                'save_card' => false,
            ],
        ], (array) $request->getModel());
    }

    public function testShouldDoNothingIfCardNotToken()
    {
        $model = new \ArrayObject([
            'card' => ['theTokenMustBeObtained'],
            'local' => [
                'save_card' => true,
            ],
        ]);
        $request = new Capture($model);

        $gatewayMock = $this->createGatewayMock();
        $gatewayMock
            ->expects($this->never())
            ->method('execute')
        ;

        $context = new Context($gatewayMock, $request, []);

        $extension = new CreateCustomerExtension();
        $extension->onPreExecute($context);

        $this->assertEquals([
            'card' => ['theTokenMustBeObtained'],
            'local' => [
                'save_card' => true,
            ],
        ], (array) $request->getModel());
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|GatewayInterface
     */
    protected function createGatewayMock()
    {
        return $this->getMock(GatewayInterface::class);
    }
}