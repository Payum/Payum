<?php

namespace Payum\Core\Tests\Request;

use Payum\Core\Request\Generic;
use Payum\Core\Storage\IdentityInterface;
use PHPUnit\Framework\TestCase;

class GenericTest extends TestCase
{
    public static function provideDifferentPhpTypes(): \Iterator
    {
        yield 'object' => [new \stdClass()];
        yield 'int' => [5];
        yield 'float' => [5.5];
        yield 'string' => ['foo'];
        yield 'boolean' => [false];
        yield 'resource' => [tmpfile()];
    }

    public function testShouldBeAbstractClass()
    {
        $rc = new \ReflectionClass(Generic::class);

        $this->assertTrue($rc->isAbstract());
    }

    public function testShouldImplementModelAwareInterface()
    {
        $rc = new \ReflectionClass(Generic::class);

        $this->assertTrue($rc->implementsInterface('Payum\Core\Model\ModelAwareInterface'));
    }

    public function testShouldImplementModelAggregateInterface()
    {
        $rc = new \ReflectionClass(Generic::class);

        $this->assertTrue($rc->implementsInterface('Payum\Core\Model\ModelAggregateInterface'));
    }

    public function testShouldImplementTokenAggregateInterface()
    {
        $rc = new \ReflectionClass(Generic::class);

        $this->assertTrue($rc->implementsInterface('Payum\Core\Security\TokenAggregateInterface'));
    }

    /**
     * @dataProvider provideDifferentPhpTypes
     */
    public function testCouldBeConstructedWithModelOfAnyType($phpType)
    {
        $request = new class($phpType) extends Generic {
        };

        $this->assertEquals($phpType, $request->getModel());
    }

    /**
     * @dataProvider provideDifferentPhpTypes
     */
    public function testShouldAllowSetModelAndGetIt($phpType)
    {
        $request = new class(123321) extends Generic {
        };

        $request->setModel($phpType);

        $this->assertEquals($phpType, $request->getModel());
    }

    /**
     * @dataProvider provideDifferentPhpTypes
     */
    public function testShouldAllowGetModelSetInConstructor($phpType)
    {
        /** @var Generic $request */
        $request = $this->getMockForAbstractClass(Generic::class, [$phpType]);

        $this->assertEquals($phpType, $request->getModel());
    }

    public function testShouldAllowGetTokenSetInConstructor()
    {
        $tokenMock = $this->createMock('Payum\Core\Security\TokenInterface');

        /** @var Generic $request */
        $request = $this->getMockForAbstractClass(Generic::class, [$tokenMock]);

        $this->assertSame($tokenMock, $request->getModel());
        $this->assertSame($tokenMock, $request->getToken());
    }

    public function testShouldConvertArrayToArrayObjectInConstructor()
    {
        $model = [
            'foo' => 'bar',
        ];

        /** @var Generic $request */
        $request = $this->getMockForAbstractClass(Generic::class, [$model]);

        $this->assertInstanceOf('ArrayObject', $request->getModel());
        $this->assertEquals($model, (array) $request->getModel());
    }

    public function testShouldConvertArrayToArrayObjectSetWithSetter()
    {
        /** @var Generic $request */
        $request = $this->getMockForAbstractClass(Generic::class, [123321]);

        $model = [
            'foo' => 'bar',
        ];

        $request->setModel($model);

        $this->assertInstanceOf('ArrayObject', $request->getModel());
        $this->assertEquals($model, (array) $request->getModel());
    }

    public function testShouldNotSetTokenAsFirstModelOnConstruct()
    {
        /** @var Generic $request */
        $token = $this->createMock('Payum\Core\Security\TokenInterface');

        $request = $this->getMockForAbstractClass(Generic::class, [$token]);

        $this->assertNull($request->getFirstModel());
    }

    public function testShouldNotSetIdentityAsFirstModelOnConstruct()
    {
        $identity = new class() implements IdentityInterface {
            public function serialize()
            {
            }

            public function unserialize($data)
            {
            }

            public function getClass()
            {
            }

            public function getId()
            {
            }

            public function __serialize(): array
            {
                return [];
            }

            public function __unserialize(array $data): void
            {
            }
        };

        $request = $this->getMockForAbstractClass(Generic::class, [$identity]);

        $this->assertNull($request->getFirstModel());
    }

    public function testShouldSetAnyObjectAsFirstModelOnConstruct()
    {
        $model = new \stdClass();

        /** @var Generic $request */
        $request = $this->getMockForAbstractClass(Generic::class, [$model]);

        $this->assertSame($model, $request->getFirstModel());
    }

    public function testShouldNotSetTokenAsFirstModelOnSetModel()
    {
        $token = $this->createMock('Payum\Core\Security\TokenInterface');

        /** @var Generic $request */
        $request = $this->getMockForAbstractClass(Generic::class, [null]);
        $request->setModel($token);

        $this->assertNull($request->getFirstModel());
    }

    public function testShouldNotSetIdentityAsFirstModelOnSetModel()
    {
        $identity = new class() implements IdentityInterface {
            public function serialize()
            {
            }

            public function unserialize($data)
            {
            }

            public function getClass()
            {
            }

            public function getId()
            {
            }

            public function __serialize(): array
            {
                return [];
            }

            public function __unserialize(array $data): void
            {
            }
        };

        /** @var Generic $request */
        $request = $this->getMockForAbstractClass(Generic::class, [null]);
        $request->setModel($identity);

        $this->assertNull($request->getFirstModel());
    }

    public function testShouldSetAnyObjectAsFirstModelOnSetModel()
    {
        $model = new \stdClass();

        /** @var Generic $request */
        $request = $this->getMockForAbstractClass(Generic::class, [null]);
        $request->setModel($model);

        $this->assertSame($model, $request->getFirstModel());
    }

    public function testShouldNotChangeFirstModelOnSecondSetModelCall()
    {
        $firstModel = new \stdClass();
        $secondModel = new \stdClass();

        /** @var Generic $request */
        $request = $this->getMockForAbstractClass(Generic::class, [$firstModel]);
        $request->setModel($secondModel);

        $this->assertSame($firstModel, $request->getFirstModel());
        $this->assertSame($secondModel, $request->getModel());
    }
}
