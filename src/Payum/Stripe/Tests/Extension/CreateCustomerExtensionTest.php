<?php

namespace Payum\Stripe\Tests\Extension;

use ArrayObject;
use Payum\Core\Extension\Context;
use Payum\Core\Extension\ExtensionInterface;
use Payum\Core\GatewayInterface;
use Payum\Core\Request\Capture;
use Payum\Core\Request\Refund;
use Payum\Stripe\Constants;
use Payum\Stripe\Extension\CreateCustomerExtension;
use Payum\Stripe\Request\Api\CreateCustomer;
use Payum\Stripe\Request\Api\ObtainToken;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

class CreateCustomerExtensionTest extends TestCase
{
    public function testShouldImplementExtensionInterface(): void
    {
        $rc = new ReflectionClass(CreateCustomerExtension::class);

        $this->assertTrue($rc->implementsInterface(ExtensionInterface::class));
    }

    public function testShouldCreateCustomerAndReplaceCardTokenOnPreCapture(): void
    {
        $model = new ArrayObject([
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
            ->willReturnCallback(function (CreateCustomer $request): void {
                $model = $request->getModel();

                $this->assertInstanceOf(ArrayObject::class, $model);

                $this->assertSame([
                    'card' => 'theCardToken',
                ], (array) $model);

                $model['id'] = 'theCustomerId';
            });

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
                ],
            ],
        ], (array) $request->getModel());
    }

    public function testShouldCreateCustomerWithCustomInfoAndReplaceCardTokenOnPreCapture(): void
    {
        $model = new ArrayObject([
            'card' => 'theCardToken',
            'local' => [
                'save_card' => true,
                'customer' => [
                    'foo' => 'fooVal',
                    'bar' => 'barVal',
                ],
            ],
        ]);
        $request = new Capture($model);

        $gatewayMock = $this->createGatewayMock();
        $gatewayMock
            ->expects($this->once())
            ->method('execute')
            ->with($this->isInstanceOf(CreateCustomer::class))
            ->willReturnCallback(function (CreateCustomer $request): void {
                $model = $request->getModel();

                $this->assertInstanceOf(ArrayObject::class, $model);

                $this->assertSame([
                    'foo' => 'fooVal',
                    'bar' => 'barVal',
                    'card' => 'theCardToken',
                ], (array) $model);

                $model['id'] = 'theCustomerId';
            });

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
                    'bar' => 'barVal',
                ],
            ],
        ], (array) $request->getModel());
    }

    public function testShouldSetStatusFailedIfCreateCustomerRequestFailedOnPreCapture(): void
    {
        $model = new ArrayObject([
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
            ->willReturnCallback(function (CreateCustomer $request): void {
                $model = $request->getModel();

                $this->assertInstanceOf(ArrayObject::class, $model);

                $this->assertSame([
                    'card' => 'theCardToken',
                ], (array) $model);

                // we assume the customer creation has failed when the customer does not have an id set.
                $model['id'] = null;
            });

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
                ],
            ],
        ], (array) $request->getModel());
    }

    public function testShouldDoNothingIfNotCaptureRequestOnPreExecute(): void
    {
        $model = new ArrayObject([
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

    public function testShouldDoNothingIfSaveCardNotSetOnPreExecute(): void
    {
        $model = new ArrayObject([
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

        $this->assertSame([
            'card' => 'theCardToken',
        ], (array) $request->getModel());
    }

    public function testShouldDoNothingIfSaveCardSetToFalseOnPreExecute(): void
    {
        $model = new ArrayObject([
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

    public function testShouldDoNothingIfCardNotTokenOnPreExecute(): void
    {
        $model = new ArrayObject([
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

    public function testShouldDoNothingIfCustomerSetOnPreExecute(): void
    {
        $model = new ArrayObject([
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

    public function testShouldCreateCustomerAndReplaceCardTokenOnPostObtainToken(): void
    {
        $model = new ArrayObject([
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
            ->willReturnCallback(function (CreateCustomer $request): void {
                $model = $request->getModel();

                $this->assertInstanceOf(ArrayObject::class, $model);

                $this->assertSame([
                    'card' => 'theCardToken',
                ], (array) $model);

                $model['id'] = 'theCustomerId';
            });

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
                ],
            ],
        ], (array) $request->getModel());
    }

    public function testShouldCreateCustomerWithCustomInfoAndReplaceCardTokenOnPostObtainToken(): void
    {
        $model = new ArrayObject([
            'card' => 'theCardToken',
            'local' => [
                'save_card' => true,
                'customer' => [
                    'foo' => 'fooVal',
                    'bar' => 'barVal',
                ],
            ],
        ]);
        $request = new ObtainToken($model);

        $gatewayMock = $this->createGatewayMock();
        $gatewayMock
            ->expects($this->once())
            ->method('execute')
            ->with($this->isInstanceOf(CreateCustomer::class))
            ->willReturnCallback(function (CreateCustomer $request): void {
                $model = $request->getModel();

                $this->assertInstanceOf(ArrayObject::class, $model);

                $this->assertSame([
                    'foo' => 'fooVal',
                    'bar' => 'barVal',
                    'card' => 'theCardToken',
                ], (array) $model);

                $model['id'] = 'theCustomerId';
            });

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
                    'bar' => 'barVal',
                ],
            ],
        ], (array) $request->getModel());
    }

    public function testShouldSetStatusFailedIfCreateCustomerRequestFailedOnPostObtainToken(): void
    {
        $model = new ArrayObject([
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
            ->willReturnCallback(function (CreateCustomer $request): void {
                $model = $request->getModel();

                $this->assertInstanceOf(ArrayObject::class, $model);

                $this->assertSame([
                    'card' => 'theCardToken',
                ], (array) $model);

                // we assume the customer creation has failed when the customer does not have an id set.
                $model['id'] = null;
            });

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
                ],
            ],
        ], (array) $request->getModel());
    }

    public function testShouldDoNothingIfNotCaptureRequestOnPostExecute(): void
    {
        $model = new ArrayObject([
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

    public function testShouldDoNothingIfSaveCardNotSetOnPostExecute(): void
    {
        $model = new ArrayObject([
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

        $this->assertSame([
            'card' => 'theCardToken',
        ], (array) $request->getModel());
    }

    public function testShouldDoNothingIfSaveCardSetToFalseOnPostExecute(): void
    {
        $model = new ArrayObject([
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

    public function testShouldDoNothingIfCardNotTokenOnPostExecute(): void
    {
        $model = new ArrayObject([
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

    public function testShouldDoNothingIfCustomerSetOnPostExecute(): void
    {
        $model = new ArrayObject([
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
     * @return MockObject|GatewayInterface
     */
    protected function createGatewayMock()
    {
        return $this->createMock(GatewayInterface::class);
    }
}
