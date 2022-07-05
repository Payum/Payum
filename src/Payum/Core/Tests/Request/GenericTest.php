<?php

namespace Payum\Core\Tests\Request;

use Payum\Core\Request\Generic;
use Payum\Core\Storage\IdentityInterface;
use PHPUnit\Framework\TestCase;

class GenericTest extends TestCase
{
    public static function provideDifferentPhpTypes(): \Iterator
    {
        yield 'object' => array(new \stdClass());
        yield 'int' => array(5);
        yield 'float' => array(5.5);
        yield 'string' => array('foo');
        yield 'boolean' => array(false);
        yield 'resource' => array(tmpfile());
    }

    /**
     * @test
     */
    public function shouldBeAbstractClass()
    {
        $rc = new \ReflectionClass(Generic::class);

        $this->assertTrue($rc->isAbstract());
    }

    /**
     * @test
     */
    public function shouldImplementModelAwareInterface()
    {
        $rc = new \ReflectionClass(Generic::class);

        $this->assertTrue($rc->implementsInterface('Payum\Core\Model\ModelAwareInterface'));
    }

    /**
     * @test
     */
    public function shouldImplementModelAggregateInterface()
    {
        $rc = new \ReflectionClass(Generic::class);

        $this->assertTrue($rc->implementsInterface('Payum\Core\Model\ModelAggregateInterface'));
    }

    /**
     * @test
     */
    public function shouldImplementTokenAggregateInterface()
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
     * @test
     *
     * @dataProvider provideDifferentPhpTypes
     */
    public function shouldAllowSetModelAndGetIt($phpType)
    {
        $request = new class(123321) extends Generic {
        };

        $request->setModel($phpType);

        $this->assertEquals($phpType, $request->getModel());
    }

    /**
     * @test
     *
     * @dataProvider provideDifferentPhpTypes
     */
    public function shouldAllowGetModelSetInConstructor($phpType)
    {
        /** @var Generic $request */
        $request = $this->getMockForAbstractClass(Generic::class, array($phpType));

        $this->assertEquals($phpType, $request->getModel());
    }

    /**
     * @test
     */
    public function shouldAllowGetTokenSetInConstructor()
    {
        $tokenMock = $this->createMock('Payum\Core\Security\TokenInterface');

        /** @var Generic $request */
        $request = $this->getMockForAbstractClass(Generic::class, array($tokenMock));

        $this->assertSame($tokenMock, $request->getModel());
        $this->assertSame($tokenMock, $request->getToken());
    }

    /**
     * @test
     */
    public function shouldConvertArrayToArrayObjectInConstructor()
    {
        $model = array('foo' => 'bar');

        /** @var Generic $request */
        $request = $this->getMockForAbstractClass(Generic::class, array($model));

        $this->assertInstanceOf('ArrayObject', $request->getModel());
        $this->assertEquals($model, (array) $request->getModel());
    }

    /**
     * @test
     */
    public function shouldConvertArrayToArrayObjectSetWithSetter()
    {
        /** @var Generic $request */
        $request = $this->getMockForAbstractClass(Generic::class, array(123321));

        $model = array('foo' => 'bar');

        $request->setModel($model);

        $this->assertInstanceOf('ArrayObject', $request->getModel());
        $this->assertEquals($model, (array) $request->getModel());
    }

    /**
     * @test
     */
    public function shouldNotSetTokenAsFirstModelOnConstruct()
    {
        /** @var Generic $request */
        $token = $this->createMock('Payum\Core\Security\TokenInterface');

        $request = $this->getMockForAbstractClass(Generic::class, array($token));

        $this->assertNull($request->getFirstModel());
    }

    /**
     * @test
     */
    public function shouldNotSetIdentityAsFirstModelOnConstruct()
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

        $request = $this->getMockForAbstractClass(Generic::class, array($identity));

        $this->assertNull($request->getFirstModel());
    }

    /**
     * @test
     */
    public function shouldSetAnyObjectAsFirstModelOnConstruct()
    {
        $model = new \stdClass();

        /** @var Generic $request */
        $request = $this->getMockForAbstractClass(Generic::class, array($model));

        $this->assertSame($model, $request->getFirstModel());
    }

    /**
     * @test
     */
    public function shouldNotSetTokenAsFirstModelOnSetModel()
    {
        $token = $this->createMock('Payum\Core\Security\TokenInterface');

        /** @var Generic $request */
        $request = $this->getMockForAbstractClass(Generic::class, array(null));
        $request->setModel($token);

        $this->assertNull($request->getFirstModel());
    }

    /**
     * @test
     */
    public function shouldNotSetIdentityAsFirstModelOnSetModel()
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
        $request = $this->getMockForAbstractClass(Generic::class, array(null));
        $request->setModel($identity);

        $this->assertNull($request->getFirstModel());
    }

    /**
     * @test
     */
    public function shouldSetAnyObjectAsFirstModelOnSetModel()
    {
        $model = new \stdClass();

        /** @var Generic $request */
        $request = $this->getMockForAbstractClass(Generic::class, array(null));
        $request->setModel($model);

        $this->assertSame($model, $request->getFirstModel());
    }

    /**
     * @test
     */
    public function shouldNotChangeFirstModelOnSecondSetModelCall()
    {
        $firstModel = new \stdClass();
        $secondModel = new \stdClass();

        /** @var Generic $request */
        $request = $this->getMockForAbstractClass(Generic::class, array($firstModel));
        $request->setModel($secondModel);

        $this->assertSame($firstModel, $request->getFirstModel());
        $this->assertSame($secondModel, $request->getModel());
    }
}
