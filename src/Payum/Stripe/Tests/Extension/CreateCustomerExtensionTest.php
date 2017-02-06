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
use Payum\Stripe\Request\Api\ObtainToken;

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

    public function testShouldCreateCustomerAndReplaceCardTokenOnPreCapture()
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
            ->willReturnCallback(function (CreateCustomer $request) {
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

    public function testShouldCreateCustomerWithCustomInfoAndReplaceCardTokenOnPreCapture()
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
            ->willReturnCallback(function (CreateCustomer $request) {
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

    public function testShouldSetStatusFailedIfCreateCustomerRequestFailedOnPreCapture()
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
            ->willReturnCallback(function (CreateCustomer $request) {
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

    public function testShouldDoNothingIfNotCaptureRequestOnPreExecute()
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

    public function testShouldDoNothingIfSaveCardNotSetOnPreExecute()
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

    public function testShouldDoNothingIfSaveCardSetToFalseOnPreExecute()
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

    public function testShouldDoNothingIfCardNotTokenOnPreExecute()
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

    public function testShouldDoNothingIfCustomerSetOnPreExecute()
    {
        $model = new \ArrayObject([
            'customer' => 'aCustomerId',
            'card' => 'theTokenMustBeObtained',
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
            'customer' => 'aCustomerId',
            'card' => 'theTokenMustBeObtained',
            'local' => [
                'save_card' => true,
            ],
        ], (array) $request->getModel());
    }

    public function testShouldCreateCustomerAndReplaceCardTokenOnPostObtainToken()
    {
        $model = new \ArrayObject([
            'card' => 'theCardToken',
            'local' => ['save_card' => true],
        ]);
        $request = new ObtainToken($model);

        $gatewayMock = $this->createGatewayMock();
        $gatewayMock
            ->expects($this->once())
            ->method('execute')
            ->with($this->isInstanceOf(CreateCustomer::class))
            ->willReturnCallback(function (CreateCustomer $request) {
                $model = $request->getModel();

                $this->assertInstanceOf(\ArrayObject::class, $model);

                $this->assertEquals(['card' => 'theCardToken'], (array) $model);

                $model['id'] = 'theCustomerId';
            });
        ;

        $context = new Context($gatewayMock, $request, []);

        $extension = new CreateCustomerExtension();
        $extension->onPostExecute($context);

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

    public function testShouldCreateCustomerWithCustomInfoAndReplaceCardTokenOnPostObtainToken()
    {
        $model = new \ArrayObject([
            'card' => 'theCardToken',
            'local' => [
                'save_card' => true,
                'customer' => ['foo' => 'fooVal', 'bar' => 'barVal'],
            ],
        ]);
        $request = new ObtainToken($model);

        $gatewayMock = $this->createGatewayMock();
        $gatewayMock
            ->expects($this->once())
            ->method('execute')
            ->with($this->isInstanceOf(CreateCustomer::class))
            ->willReturnCallback(function (CreateCustomer $request) {
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
        $extension->onPostExecute($context);

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

    public function testShouldSetStatusFailedIfCreateCustomerRequestFailedOnPostObtainToken()
    {
        $model = new \ArrayObject([
            'card' => 'theCardToken',
            'local' => [
                'save_card' => true,
            ],
        ]);
        $request = new ObtainToken($model);

        $gatewayMock = $this->createGatewayMock();
        $gatewayMock
            ->expects($this->once())
            ->method('execute')
            ->with($this->isInstanceOf(CreateCustomer::class))
            ->willReturnCallback(function (CreateCustomer $request) {
                $model = $request->getModel();

                $this->assertInstanceOf(\ArrayObject::class, $model);

                $this->assertEquals(['card' => 'theCardToken'], (array) $model);

                // we assume the customer creation has failed when the customer does not have an id set.
                $model['id'] = null;
            });
        ;

        $context = new Context($gatewayMock, $request, []);

        $extension = new CreateCustomerExtension();
        $extension->onPostExecute($context);

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

    public function testShouldDoNothingIfNotCaptureRequestOnPostExecute()
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
        $extension->onPostExecute($context);

        $this->assertEquals([
            'card' => 'theCardToken',
            'local' => [
                'save_card' => true,
            ],
        ], (array) $request->getModel());
    }

    public function testShouldDoNothingIfSaveCardNotSetOnPostExecute()
    {
        $model = new \ArrayObject([
            'card' => 'theCardToken',
        ]);
        $request = new ObtainToken($model);

        $gatewayMock = $this->createGatewayMock();
        $gatewayMock
            ->expects($this->never())
            ->method('execute')
        ;

        $context = new Context($gatewayMock, $request, []);

        $extension = new CreateCustomerExtension();
        $extension->onPostExecute($context);

        $this->assertEquals([
            'card' => 'theCardToken',
        ], (array) $request->getModel());
    }

    public function testShouldDoNothingIfSaveCardSetToFalseOnPostExecute()
    {
        $model = new \ArrayObject([
            'card' => 'theCardToken',
            'local' => [
                'save_card' => false,
            ],
        ]);
        $request = new ObtainToken($model);

        $gatewayMock = $this->createGatewayMock();
        $gatewayMock
            ->expects($this->never())
            ->method('execute')
        ;

        $context = new Context($gatewayMock, $request, []);

        $extension = new CreateCustomerExtension();
        $extension->onPostExecute($context);

        $this->assertEquals([
            'card' => 'theCardToken',
            'local' => [
                'save_card' => false,
            ],
        ], (array) $request->getModel());
    }

    public function testShouldDoNothingIfCardNotTokenOnPostExecute()
    {
        $model = new \ArrayObject([
            'card' => ['theTokenMustBeObtained'],
            'local' => [
                'save_card' => true,
            ],
        ]);
        $request = new ObtainToken($model);

        $gatewayMock = $this->createGatewayMock();
        $gatewayMock
            ->expects($this->never())
            ->method('execute')
        ;

        $context = new Context($gatewayMock, $request, []);

        $extension = new CreateCustomerExtension();
        $extension->onPostExecute($context);

        $this->assertEquals([
            'card' => ['theTokenMustBeObtained'],
            'local' => [
                'save_card' => true,
            ],
        ], (array) $request->getModel());
    }

    public function testShouldDoNothingIfCustomerSetOnPostExecute()
    {
        $model = new \ArrayObject([
            'customer' => 'aCustomerId',
            'card' => 'theTokenMustBeObtained',
            'local' => [
                'save_card' => true,
            ],
        ]);
        $request = new ObtainToken($model);

        $gatewayMock = $this->createGatewayMock();
        $gatewayMock
            ->expects($this->never())
            ->method('execute')
        ;

        $context = new Context($gatewayMock, $request, []);

        $extension = new CreateCustomerExtension();
        $extension->onPostExecute($context);

        $this->assertEquals([
            'customer' => 'aCustomerId',
            'card' => 'theTokenMustBeObtained',
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
