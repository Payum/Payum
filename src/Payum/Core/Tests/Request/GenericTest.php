<?php

namespace Payum\Core\Tests\Request;

use ArrayObject;
use Iterator;
use Payum\Core\Model\ModelAggregateInterface;
use Payum\Core\Model\ModelAwareInterface;
use Payum\Core\Request\Generic;
use Payum\Core\Security\TokenAggregateInterface;
use Payum\Core\Security\TokenInterface;
use Payum\Core\Storage\IdentityInterface;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use stdClass;

class GenericTest extends TestCase
{
    public static function provideDifferentPhpTypes(): Iterator
    {
        yield 'object' => [new stdClass()];
        yield 'int' => [5];
        yield 'float' => [5.5];
        yield 'string' => ['foo'];
        yield 'boolean' => [false];
        yield 'resource' => [tmpfile()];
    }

    public function testShouldBeAbstractClass(): void
    {
        $rc = new ReflectionClass(Generic::class);

        $this->assertTrue($rc->isAbstract());
    }

    public function testShouldImplementModelAwareInterface(): void
    {
        $rc = new ReflectionClass(Generic::class);

        $this->assertTrue($rc->implementsInterface(ModelAwareInterface::class));
    }

    public function testShouldImplementModelAggregateInterface(): void
    {
        $rc = new ReflectionClass(Generic::class);

        $this->assertTrue($rc->implementsInterface(ModelAggregateInterface::class));
    }

    public function testShouldImplementTokenAggregateInterface(): void
    {
        $rc = new ReflectionClass(Generic::class);

        $this->assertTrue($rc->implementsInterface(TokenAggregateInterface::class));
    }

    /**
     * @dataProvider provideDifferentPhpTypes
     */
    public function testCouldBeConstructedWithModelOfAnyType($phpType): void
    {
        $request = new class($phpType) extends Generic {
        };

        $this->assertEquals($phpType, $request->getModel());
    }

    /**
     * @dataProvider provideDifferentPhpTypes
     */
    public function testShouldAllowSetModelAndGetIt($phpType): void
    {
        $request = new class(123321) extends Generic {
        };

        $request->setModel($phpType);

        $this->assertEquals($phpType, $request->getModel());
    }

    /**
     * @dataProvider provideDifferentPhpTypes
     */
    public function testShouldAllowGetModelSetInConstructor($phpType): void
    {
        /** @var Generic $request */
        $request = $this->getMockForAbstractClass(Generic::class, [$phpType]);

        $this->assertEquals($phpType, $request->getModel());
    }

    public function testShouldAllowGetTokenSetInConstructor(): void
    {
        $tokenMock = $this->createMock(TokenInterface::class);

        /** @var Generic $request */
        $request = $this->getMockForAbstractClass(Generic::class, [$tokenMock]);

        $this->assertSame($tokenMock, $request->getModel());
        $this->assertSame($tokenMock, $request->getToken());
    }

    public function testShouldConvertArrayToArrayObjectInConstructor(): void
    {
        $model = [
            'foo' => 'bar',
        ];

        /** @var Generic $request */
        $request = $this->getMockForAbstractClass(Generic::class, [$model]);

        $this->assertInstanceOf(ArrayObject::class, $request->getModel());
        $this->assertEquals($model, (array) $request->getModel());
    }

    public function testShouldConvertArrayToArrayObjectSetWithSetter(): void
    {
        /** @var Generic $request */
        $request = $this->getMockForAbstractClass(Generic::class, [123321]);

        $model = [
            'foo' => 'bar',
        ];

        $request->setModel($model);

        $this->assertInstanceOf(ArrayObject::class, $request->getModel());
        $this->assertEquals($model, (array) $request->getModel());
    }

    public function testShouldNotSetTokenAsFirstModelOnConstruct(): void
    {
        /** @var Generic $request */
        $token = $this->createMock(TokenInterface::class);

        $request = $this->getMockForAbstractClass(Generic::class, [$token]);

        $this->assertNull($request->getFirstModel());
    }

    public function testShouldNotSetIdentityAsFirstModelOnConstruct(): void
    {
        $identity = new class() implements IdentityInterface {
            public function serialize()
            {
                return serialize(null);
            }

            public function unserialize($data): void
            {
            }

            public function getClass(): string
            {
                return \stdClass::class;
            }

            public function getId(): mixed
            {
                return 1;
            }

            /**
             * @return array<string, mixed>
             */
            public function __serialize(): array
            {
                return [];
            }

            /**
             * @param array<string, mixed> $data
             */
            public function __unserialize(array $data): void
            {
            }
        };

        $request = $this->getMockForAbstractClass(Generic::class, [$identity]);

        $this->assertNull($request->getFirstModel());
    }

    public function testShouldSetAnyObjectAsFirstModelOnConstruct(): void
    {
        $model = new stdClass();

        /** @var Generic $request */
        $request = $this->getMockForAbstractClass(Generic::class, [$model]);

        $this->assertSame($model, $request->getFirstModel());
    }

    public function testShouldNotSetTokenAsFirstModelOnSetModel(): void
    {
        $token = $this->createMock(TokenInterface::class);

        /** @var Generic $request */
        $request = $this->getMockForAbstractClass(Generic::class, [null]);
        $request->setModel($token);

        $this->assertNull($request->getFirstModel());
    }

    public function testShouldNotSetIdentityAsFirstModelOnSetModel(): void
    {
        $identity = new class() implements IdentityInterface {
            public function serialize()
            {
                return serialize(null);
            }

            public function unserialize($data): void
            {
            }

            public function getClass(): string
            {
                return \stdClass::class;
            }

            public function getId(): mixed
            {
                return 1;
            }

            /**
             * @return array<string, mixed>
             */
            public function __serialize(): array
            {
                return [];
            }

            /**
             * @param array<string, mixed> $data
             */
            public function __unserialize(array $data): void
            {
            }
        };

        /** @var Generic $request */
        $request = $this->getMockForAbstractClass(Generic::class, [null]);
        $request->setModel($identity);

        $this->assertNull($request->getFirstModel());
    }

    public function testShouldSetAnyObjectAsFirstModelOnSetModel(): void
    {
        $model = new stdClass();

        /** @var Generic $request */
        $request = $this->getMockForAbstractClass(Generic::class, [null]);
        $request->setModel($model);

        $this->assertSame($model, $request->getFirstModel());
    }

    public function testShouldNotChangeFirstModelOnSecondSetModelCall(): void
    {
        $firstModel = new stdClass();
        $secondModel = new stdClass();

        /** @var Generic $request */
        $request = $this->getMockForAbstractClass(Generic::class, [$firstModel]);
        $request->setModel($secondModel);

        $this->assertSame($firstModel, $request->getFirstModel());
        $this->assertSame($secondModel, $request->getModel());
    }
}
