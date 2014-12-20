<?php
namespace Payum\Core\Tests\Security;

use Payum\Core\Model\Identity;
use Payum\Core\Model\Token;
use Payum\Core\Registry\StorageRegistryInterface;
use Payum\Core\Security\GenericTokenFactory;
use Payum\Core\Storage\StorageInterface;

class GenericTokenFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldImplementsGenericTokenFactoryInterface()
    {
        $rc = new \ReflectionClass('Payum\Core\Security\GenericTokenFactory');

        $this->assertTrue($rc->implementsInterface('Payum\Core\Security\GenericTokenFactoryInterface'));
    }

    /**
     * @test
     */
    public function couldBeConstructedWithExpectedArguments()
    {
        new GenericTokenFactory(
            $this->createStorageMock(),
            $this->createStorageRegistryMock(),
            'http://example.com',
            'capture.php',
            'notify.php',
            'authorize.php',
            'refund.php'
        );
    }

    /**
     * @test
     */
    public function shouldCreateCustomToken()
    {
        $token = new Token();

        $tokenStorageMock = $this->createStorageMock();
        $tokenStorageMock
            ->expects($this->once())
            ->method('create')
            ->will($this->returnValue($token))
        ;
        $tokenStorageMock
            ->expects($this->once())
            ->method('update')
            ->with($this->identicalTo($token))
        ;

        $model = new \stdClass();
        $identity = new Identity('anId', 'stdClass');
        $paymentName = 'thePaymentName';

        $modelStorage = $this->createStorageMock();
        $modelStorage
            ->expects($this->once())
            ->method('identify')
            ->with($this->identicalTo($model))
            ->will($this->returnValue($identity))
        ;

        $storageRegistryMock = $this->createStorageRegistryMock();
        $storageRegistryMock
            ->expects($this->once())
            ->method('getStorage')
            ->with($this->identicalTo($model))
            ->will($this->returnValue($modelStorage))
        ;

        $factory = new GenericTokenFactory(
            $tokenStorageMock,
            $storageRegistryMock,
            'http://example.com',
            'capture.php',
            'notify.php',
            'authorize.php',
            'refund.php'
        );

        $actualToken = $factory->createToken(
            $paymentName,
            $model,
            'theTargetPath',
            array('targetPathKey' => 'targetPathVal'),
            'theAfterPath',
            array('afterPathKey' => 'afterPathVal', 'payum_token' => null)
        );

        $this->assertSame($token, $actualToken);
        $this->assertEquals($paymentName, $token->getPaymentName());
        $this->assertSame($identity, $token->getDetails());
        $this->assertEquals(
            'http://example.com/theTargetPath?payum_token='.$token->getHash().'&targetPathKey=targetPathVal',
            $token->getTargetUrl()
        );
        $this->assertEquals('http://example.com/theAfterPath?afterPathKey=afterPathVal', $token->getAfterUrl());
    }

    /**
     * @test
     */
    public function shouldCreateCustomTokenWithIdentityAsModel()
    {
        $token = new Token();

        $tokenStorageMock = $this->createStorageMock();
        $tokenStorageMock
            ->expects($this->once())
            ->method('create')
            ->will($this->returnValue($token))
        ;
        $tokenStorageMock
            ->expects($this->once())
            ->method('update')
            ->with($this->identicalTo($token))
        ;

        $paymentName = 'thePaymentName';
        $identity = new Identity('anId', 'stdClass');

        $storageRegistryMock = $this->createStorageRegistryMock();
        $storageRegistryMock
            ->expects($this->never())
            ->method('getStorage')
        ;

        $factory = new GenericTokenFactory(
            $tokenStorageMock,
            $storageRegistryMock,
            'http://example.com',
            'capture.php',
            'notify.php',
            'authorize.php',
            'refund.php'
        );

        $actualToken = $factory->createToken(
            $paymentName,
            $identity,
            'theTargetPath',
            array('targetPathKey' => 'targetPathVal'),
            'theAfterPath',
            array('afterPathKey' => 'afterPathVal')
        );

        $this->assertSame($token, $actualToken);
        $this->assertSame($identity, $token->getDetails());
    }

    /**
     * @test
     */
    public function shouldCreateCustomTokenWithTargetPathAlreadyUrl()
    {
        $token = new Token();

        $tokenStorageMock = $this->createStorageMock();
        $tokenStorageMock
            ->expects($this->once())
            ->method('create')
            ->will($this->returnValue($token))
        ;
        $tokenStorageMock
            ->expects($this->once())
            ->method('update')
            ->with($this->identicalTo($token))
        ;

        $model = new \stdClass();
        $identity = new Identity('anId', 'stdClass');
        $paymentName = 'thePaymentName';

        $modelStorage = $this->createStorageMock();
        $modelStorage
            ->expects($this->once())
            ->method('identify')
            ->with($this->identicalTo($model))
            ->will($this->returnValue($identity))
        ;

        $storageRegistryMock = $this->createStorageRegistryMock();
        $storageRegistryMock
            ->expects($this->once())
            ->method('getStorage')
            ->with($this->identicalTo($model))
            ->will($this->returnValue($modelStorage))
        ;

        $factory = new GenericTokenFactory(
            $tokenStorageMock,
            $storageRegistryMock,
            'http://example.com',
            'capture.php',
            'notify.php',
            'authorize.php',
            'refund.php'
        );

        $actualToken = $factory->createToken(
            $paymentName,
            $model,
            'http://google.com?foo=fooVal',
            array('targetPathKey' => 'targetPathVal'),
            'theAfterPath',
            array('afterPathKey' => 'afterPathVal')
        );

        $this->assertSame($token, $actualToken);
        $this->assertEquals($paymentName, $token->getPaymentName());
        $this->assertSame($identity, $token->getDetails());
        $this->assertEquals(
            'http://google.com/?payum_token='.$token->getHash().'&foo=fooVal&targetPathKey=targetPathVal',
            $token->getTargetUrl()
        );
        $this->assertEquals('http://example.com/theAfterPath?afterPathKey=afterPathVal', $token->getAfterUrl());
    }

    /**
     * @test
     */
    public function shouldCreateCustomTokenWithoutAfterUrl()
    {
        $token = new Token();

        $tokenStorageMock = $this->createStorageMock();
        $tokenStorageMock
            ->expects($this->once())
            ->method('create')
            ->will($this->returnValue($token))
        ;
        $tokenStorageMock
            ->expects($this->once())
            ->method('update')
            ->with($this->identicalTo($token))
        ;

        $model = new \stdClass();
        $identity = new Identity('anId', 'stdClass');
        $paymentName = 'thePaymentName';

        $modelStorage = $this->createStorageMock();
        $modelStorage
            ->expects($this->once())
            ->method('identify')
            ->with($this->identicalTo($model))
            ->will($this->returnValue($identity))
        ;

        $storageRegistryMock = $this->createStorageRegistryMock();
        $storageRegistryMock
            ->expects($this->once())
            ->method('getStorage')
            ->with($this->identicalTo($model))
            ->will($this->returnValue($modelStorage))
        ;

        $factory = new GenericTokenFactory(
            $tokenStorageMock,
            $storageRegistryMock,
            'http://example.com',
            'capture.php',
            'notify.php',
            'authorize.php',
            'refund.php'
        );

        $actualToken = $factory->createToken($paymentName, $model, 'theTargetPath');

        $this->assertSame($token, $actualToken);
        $this->assertEquals($paymentName, $token->getPaymentName());
        $this->assertSame($identity, $token->getDetails());
        $this->assertEquals(
            'http://example.com/theTargetPath?payum_token='.$token->getHash(),
            $token->getTargetUrl()
        );
        $this->assertNull($token->getAfterUrl());
    }

    /**
     * @test
     */
    public function shouldCreateNotifyToken()
    {
        $token = new Token();

        $tokenStorageMock = $this->createStorageMock();
        $tokenStorageMock
            ->expects($this->once())
            ->method('create')
            ->will($this->returnValue($token))
        ;
        $tokenStorageMock
            ->expects($this->once())
            ->method('update')
            ->with($this->identicalTo($token))
        ;

        $model = new \stdClass();
        $identity = new Identity('anId', 'stdClass');
        $paymentName = 'thePaymentName';

        $modelStorage = $this->createStorageMock();
        $modelStorage
            ->expects($this->once())
            ->method('identify')
            ->with($this->identicalTo($model))
            ->will($this->returnValue($identity))
        ;

        $storageRegistryMock = $this->createStorageRegistryMock();
        $storageRegistryMock
            ->expects($this->once())
            ->method('getStorage')
            ->with($this->identicalTo($model))
            ->will($this->returnValue($modelStorage))
        ;

        $factory = new GenericTokenFactory(
            $tokenStorageMock,
            $storageRegistryMock,
            'http://example.com',
            'capture.php',
            'notify.php',
            'authorize.php',
            'refund.php'
        );

        $actualToken = $factory->createNotifyToken($paymentName, $model);

        $this->assertSame($token, $actualToken);
        $this->assertEquals($paymentName, $token->getPaymentName());
        $this->assertSame($identity, $token->getDetails());
        $this->assertEquals(
            'http://example.com/notify.php?payum_token='.$token->getHash(),
            $token->getTargetUrl()
        );
        $this->assertNull($token->getAfterUrl());
    }

    /**
     * @test
     */
    public function shouldCreateNotifyTokenWithoutModel()
    {
        $token = new Token();

        $tokenStorageMock = $this->createStorageMock();
        $tokenStorageMock
            ->expects($this->once())
            ->method('create')
            ->will($this->returnValue($token))
        ;
        $tokenStorageMock
            ->expects($this->once())
            ->method('update')
            ->with($this->identicalTo($token))
        ;

        $paymentName = 'thePaymentName';

        $storageRegistryMock = $this->createStorageRegistryMock();
        $storageRegistryMock
            ->expects($this->never())
            ->method('getStorage')
        ;

        $factory = new GenericTokenFactory(
            $tokenStorageMock,
            $storageRegistryMock,
            'http://example.com',
            'capture.php',
            'notify.php',
            'authorize.php',
            'refund.php'
        );

        $actualToken = $factory->createNotifyToken($paymentName, null);

        $this->assertSame($token, $actualToken);
        $this->assertEquals($paymentName, $token->getPaymentName());
        $this->assertNull($token->getDetails());
        $this->assertEquals(
            'http://example.com/notify.php?payum_token='.$token->getHash(),
            $token->getTargetUrl()
        );
        $this->assertNull($token->getAfterUrl());
    }

    /**
     * @test
     */
    public function shouldCreateCaptureToken()
    {
        $captureToken = new Token();
        $afterToken = new Token();

        $tokenStorageMock = $this->createStorageMock();
        $tokenStorageMock
            ->expects($this->at(0))
            ->method('create')
            ->will($this->returnValue($afterToken))
        ;
        $tokenStorageMock
            ->expects($this->at(1))
            ->method('update')
            ->with($this->identicalTo($afterToken))
        ;
        $tokenStorageMock
            ->expects($this->at(2))
            ->method('create')
            ->will($this->returnValue($captureToken))
        ;
        $tokenStorageMock
            ->expects($this->at(3))
            ->method('update')
            ->with($this->identicalTo($captureToken))
        ;
        $tokenStorageMock
            ->expects($this->at(4))
            ->method('update')
            ->with($this->identicalTo($captureToken))
        ;

        $model = new \stdClass();
        $identity = new Identity('anId', 'stdClass');
        $paymentName = 'thePaymentName';

        $modelStorage = $this->createStorageMock();
        $modelStorage
            ->expects($this->exactly(2))
            ->method('identify')
            ->with($this->identicalTo($model))
            ->will($this->returnValue($identity))
        ;

        $storageRegistryMock = $this->createStorageRegistryMock();
        $storageRegistryMock
            ->expects($this->exactly(2))
            ->method('getStorage')
            ->with($this->identicalTo($model))
            ->will($this->returnValue($modelStorage))
        ;

        $factory = new GenericTokenFactory(
            $tokenStorageMock,
            $storageRegistryMock,
            'http://example.com',
            'capture.php',
            'notify.php',
            'authorize.php',
            'refund.php'
        );

        $actualToken = $factory->createCaptureToken($paymentName, $model, 'after.php', array('afterKey' => 'afterVal'));

        $this->assertSame($captureToken, $actualToken);
        $this->assertEquals($paymentName, $captureToken->getPaymentName());
        $this->assertSame($identity, $captureToken->getDetails());
        $this->assertEquals(
            'http://example.com/capture.php?payum_token='.$captureToken->getHash(),
            $captureToken->getTargetUrl()
        );
        $this->assertEquals(
            'http://example.com/after.php?payum_token='.$afterToken->getHash().'&afterKey=afterVal',
            $captureToken->getAfterUrl()
        );
    }

    /**
     * @test
     */
    public function shouldCreateCaptureTokenWithAfterPathAlreadyUrl()
    {
        $captureToken = new Token();
        $afterToken = new Token();

        $tokenStorageMock = $this->createStorageMock();
        $tokenStorageMock
            ->expects($this->at(0))
            ->method('create')
            ->will($this->returnValue($afterToken))
        ;
        $tokenStorageMock
            ->expects($this->at(1))
            ->method('update')
            ->with($this->identicalTo($afterToken))
        ;
        $tokenStorageMock
            ->expects($this->at(2))
            ->method('create')
            ->will($this->returnValue($captureToken))
        ;
        $tokenStorageMock
            ->expects($this->at(3))
            ->method('update')
            ->with($this->identicalTo($captureToken))
        ;
        $tokenStorageMock
            ->expects($this->at(4))
            ->method('update')
            ->with($this->identicalTo($captureToken))
        ;

        $model = new \stdClass();
        $identity = new Identity('anId', 'stdClass');
        $paymentName = 'thePaymentName';

        $modelStorage = $this->createStorageMock();
        $modelStorage
            ->expects($this->exactly(2))
            ->method('identify')
            ->with($this->identicalTo($model))
            ->will($this->returnValue($identity))
        ;

        $storageRegistryMock = $this->createStorageRegistryMock();
        $storageRegistryMock
            ->expects($this->exactly(2))
            ->method('getStorage')
            ->with($this->identicalTo($model))
            ->will($this->returnValue($modelStorage))
        ;

        $factory = new GenericTokenFactory(
            $tokenStorageMock,
            $storageRegistryMock,
            'http://example.com',
            'capture.php',
            'notify.php',
            'authorize.php',
            'refund.php'
        );

        $actualToken = $factory->createCaptureToken($paymentName, $model, 'http://google.com', array('afterKey' => 'afterVal'));

        $this->assertSame($captureToken, $actualToken);
        $this->assertEquals($paymentName, $captureToken->getPaymentName());
        $this->assertSame($identity, $captureToken->getDetails());
        $this->assertEquals(
            'http://example.com/capture.php?payum_token='.$captureToken->getHash(),
            $captureToken->getTargetUrl()
        );
        $this->assertEquals(
            'http://google.com/?payum_token='.$afterToken->getHash().'&afterKey=afterVal',
            $captureToken->getAfterUrl()
        );
    }

    /**
     * @test
     */
    public function shouldCreateAuthorizeToken()
    {
        $authorizeToken = new Token();
        $afterToken = new Token();

        $tokenStorageMock = $this->createStorageMock();
        $tokenStorageMock
            ->expects($this->at(0))
            ->method('create')
            ->will($this->returnValue($afterToken))
        ;
        $tokenStorageMock
            ->expects($this->at(1))
            ->method('update')
            ->with($this->identicalTo($afterToken))
        ;
        $tokenStorageMock
            ->expects($this->at(2))
            ->method('create')
            ->will($this->returnValue($authorizeToken))
        ;
        $tokenStorageMock
            ->expects($this->at(3))
            ->method('update')
            ->with($this->identicalTo($authorizeToken))
        ;
        $tokenStorageMock
            ->expects($this->at(4))
            ->method('update')
            ->with($this->identicalTo($authorizeToken))
        ;

        $model = new \stdClass();
        $identity = new Identity('anId', 'stdClass');
        $paymentName = 'thePaymentName';

        $modelStorage = $this->createStorageMock();
        $modelStorage
            ->expects($this->exactly(2))
            ->method('identify')
            ->with($this->identicalTo($model))
            ->will($this->returnValue($identity))
        ;

        $storageRegistryMock = $this->createStorageRegistryMock();
        $storageRegistryMock
            ->expects($this->exactly(2))
            ->method('getStorage')
            ->with($this->identicalTo($model))
            ->will($this->returnValue($modelStorage))
        ;

        $factory = new GenericTokenFactory(
            $tokenStorageMock,
            $storageRegistryMock,
            'http://example.com',
            'capture.php',
            'notify.php',
            'authorize.php',
            'refund.php'
        );

        $actualToken = $factory->createAuthorizeToken($paymentName, $model, 'after.php', array('afterKey' => 'afterVal'));

        $this->assertSame($authorizeToken, $actualToken);
        $this->assertEquals($paymentName, $authorizeToken->getPaymentName());
        $this->assertSame($identity, $authorizeToken->getDetails());
        $this->assertEquals(
            'http://example.com/authorize.php?payum_token='.$authorizeToken->getHash(),
            $authorizeToken->getTargetUrl()
        );
        $this->assertEquals(
            'http://example.com/after.php?payum_token='.$afterToken->getHash().'&afterKey=afterVal',
            $authorizeToken->getAfterUrl()
        );
    }

    /**
     * @test
     */
    public function shouldCreateAuthorizeTokenWithAfterPathAlreadyUrl()
    {
        $authorizeToken = new Token();
        $afterToken = new Token();

        $tokenStorageMock = $this->createStorageMock();
        $tokenStorageMock
            ->expects($this->at(0))
            ->method('create')
            ->will($this->returnValue($afterToken))
        ;
        $tokenStorageMock
            ->expects($this->at(1))
            ->method('update')
            ->with($this->identicalTo($afterToken))
        ;
        $tokenStorageMock
            ->expects($this->at(2))
            ->method('create')
            ->will($this->returnValue($authorizeToken))
        ;
        $tokenStorageMock
            ->expects($this->at(3))
            ->method('update')
            ->with($this->identicalTo($authorizeToken))
        ;
        $tokenStorageMock
            ->expects($this->at(4))
            ->method('update')
            ->with($this->identicalTo($authorizeToken))
        ;

        $model = new \stdClass();
        $identity = new Identity('anId', 'stdClass');
        $paymentName = 'thePaymentName';

        $modelStorage = $this->createStorageMock();
        $modelStorage
            ->expects($this->exactly(2))
            ->method('identify')
            ->with($this->identicalTo($model))
            ->will($this->returnValue($identity))
        ;

        $storageRegistryMock = $this->createStorageRegistryMock();
        $storageRegistryMock
            ->expects($this->exactly(2))
            ->method('getStorage')
            ->with($this->identicalTo($model))
            ->will($this->returnValue($modelStorage))
        ;

        $factory = new GenericTokenFactory(
            $tokenStorageMock,
            $storageRegistryMock,
            'http://example.com',
            'capture.php',
            'notify.php',
            'authorize.php',
            'refund.php'
        );

        $actualToken = $factory->createAuthorizeToken($paymentName, $model, 'http://google.com', array('afterKey' => 'afterVal'));

        $this->assertSame($authorizeToken, $actualToken);
        $this->assertEquals($paymentName, $authorizeToken->getPaymentName());
        $this->assertSame($identity, $authorizeToken->getDetails());
        $this->assertEquals(
            'http://example.com/authorize.php?payum_token='.$authorizeToken->getHash(),
            $authorizeToken->getTargetUrl()
        );
        $this->assertEquals(
            'http://google.com/?payum_token='.$afterToken->getHash().'&afterKey=afterVal',
            $authorizeToken->getAfterUrl()
        );
    }

    /**
     * @test
     */
    public function shouldNotOverwritePayumTokenHashInAfterUrl()
    {
        $authorizeToken = new Token();
        $afterToken = new Token();

        $tokenStorageMock = $this->createStorageMock();
        $tokenStorageMock
            ->expects($this->at(0))
            ->method('create')
            ->will($this->returnValue($afterToken))
        ;
        $tokenStorageMock
            ->expects($this->at(1))
            ->method('update')
            ->with($this->identicalTo($afterToken))
        ;
        $tokenStorageMock
            ->expects($this->at(2))
            ->method('create')
            ->will($this->returnValue($authorizeToken))
        ;
        $tokenStorageMock
            ->expects($this->at(3))
            ->method('update')
            ->with($this->identicalTo($authorizeToken))
        ;
        $tokenStorageMock
            ->expects($this->at(4))
            ->method('update')
            ->with($this->identicalTo($authorizeToken))
        ;

        $model = new \stdClass();
        $identity = new Identity('anId', 'stdClass');
        $paymentName = 'thePaymentName';

        $modelStorage = $this->createStorageMock();
        $modelStorage
            ->expects($this->exactly(2))
            ->method('identify')
            ->with($this->identicalTo($model))
            ->will($this->returnValue($identity))
        ;

        $storageRegistryMock = $this->createStorageRegistryMock();
        $storageRegistryMock
            ->expects($this->exactly(2))
            ->method('getStorage')
            ->with($this->identicalTo($model))
            ->will($this->returnValue($modelStorage))
        ;

        $factory = new GenericTokenFactory(
            $tokenStorageMock,
            $storageRegistryMock,
            'http://example.com',
            'capture.php',
            'notify.php',
            'authorize.php',
            'refund.php'
        );

        $actualToken = $factory->createAuthorizeToken($paymentName, $model, 'http://google.com/?payum_token=foo', array('afterKey' => 'afterVal'));

        $this->assertSame($authorizeToken, $actualToken);
        $this->assertEquals($paymentName, $authorizeToken->getPaymentName());
        $this->assertSame($identity, $authorizeToken->getDetails());
        $this->assertEquals(
            'http://example.com/authorize.php?payum_token='.$authorizeToken->getHash(),
            $authorizeToken->getTargetUrl()
        );
        $this->assertEquals(
            'http://google.com/?payum_token=foo&afterKey=afterVal',
            $authorizeToken->getAfterUrl()
        );
    }

    /**
     * @test
     */
    public function shouldAllowCreateAfterUrlWithoutPayumToken()
    {
        $authorizeToken = new Token();
        $afterToken = new Token();

        $tokenStorageMock = $this->createStorageMock();
        $tokenStorageMock
            ->expects($this->at(0))
            ->method('create')
            ->will($this->returnValue($afterToken))
        ;
        $tokenStorageMock
            ->expects($this->at(1))
            ->method('update')
            ->with($this->identicalTo($afterToken))
        ;
        $tokenStorageMock
            ->expects($this->at(2))
            ->method('create')
            ->will($this->returnValue($authorizeToken))
        ;
        $tokenStorageMock
            ->expects($this->at(3))
            ->method('update')
            ->with($this->identicalTo($authorizeToken))
        ;
        $tokenStorageMock
            ->expects($this->at(4))
            ->method('update')
            ->with($this->identicalTo($authorizeToken))
        ;

        $model = new \stdClass();
        $identity = new Identity('anId', 'stdClass');
        $paymentName = 'thePaymentName';

        $modelStorage = $this->createStorageMock();
        $modelStorage
            ->expects($this->exactly(2))
            ->method('identify')
            ->with($this->identicalTo($model))
            ->will($this->returnValue($identity))
        ;

        $storageRegistryMock = $this->createStorageRegistryMock();
        $storageRegistryMock
            ->expects($this->exactly(2))
            ->method('getStorage')
            ->with($this->identicalTo($model))
            ->will($this->returnValue($modelStorage))
        ;

        $factory = new GenericTokenFactory(
            $tokenStorageMock,
            $storageRegistryMock,
            'http://example.com',
            'capture.php',
            'notify.php',
            'authorize.php',
            'refund.php'
        );

        $actualToken = $factory->createAuthorizeToken($paymentName, $model, 'http://google.com/?payum_token=foo', array('payum_token' => null, 'afterKey' => 'afterVal'));

        $this->assertSame($authorizeToken, $actualToken);
        $this->assertEquals($paymentName, $authorizeToken->getPaymentName());
        $this->assertSame($identity, $authorizeToken->getDetails());
        $this->assertEquals(
            'http://example.com/authorize.php?payum_token='.$authorizeToken->getHash(),
            $authorizeToken->getTargetUrl()
        );
        $this->assertEquals(
            'http://google.com/?afterKey=afterVal',
            $authorizeToken->getAfterUrl()
        );
    }

    /**
     * @test
     */
    public function shouldAllowCreateAfterUrlWithFragment()
    {
        $authorizeToken = new Token();
        $afterToken = new Token();

        $tokenStorageMock = $this->createStorageMock();
        $tokenStorageMock
            ->expects($this->at(0))
            ->method('create')
            ->will($this->returnValue($afterToken))
        ;
        $tokenStorageMock
            ->expects($this->at(1))
            ->method('update')
            ->with($this->identicalTo($afterToken))
        ;
        $tokenStorageMock
            ->expects($this->at(2))
            ->method('create')
            ->will($this->returnValue($authorizeToken))
        ;
        $tokenStorageMock
            ->expects($this->at(3))
            ->method('update')
            ->with($this->identicalTo($authorizeToken))
        ;
        $tokenStorageMock
            ->expects($this->at(4))
            ->method('update')
            ->with($this->identicalTo($authorizeToken))
        ;

        $model = new \stdClass();
        $identity = new Identity('anId', 'stdClass');
        $paymentName = 'thePaymentName';

        $modelStorage = $this->createStorageMock();
        $modelStorage
            ->expects($this->exactly(2))
            ->method('identify')
            ->with($this->identicalTo($model))
            ->will($this->returnValue($identity))
        ;

        $storageRegistryMock = $this->createStorageRegistryMock();
        $storageRegistryMock
            ->expects($this->exactly(2))
            ->method('getStorage')
            ->with($this->identicalTo($model))
            ->will($this->returnValue($modelStorage))
        ;

        $factory = new GenericTokenFactory(
            $tokenStorageMock,
            $storageRegistryMock,
            'http://example.com',
            'capture.php',
            'notify.php',
            'authorize.php',
            'refund.php'
        );

        $actualToken = $factory->createAuthorizeToken($paymentName, $model, 'http://google.com/foo/bar?foo=fooVal#fragment', array('payum_token' => null));

        $this->assertSame($authorizeToken, $actualToken);
        $this->assertEquals($paymentName, $authorizeToken->getPaymentName());
        $this->assertSame($identity, $authorizeToken->getDetails());
        $this->assertEquals(
            'http://example.com/authorize.php?payum_token='.$authorizeToken->getHash(),
            $authorizeToken->getTargetUrl()
        );
        $this->assertEquals(
            'http://google.com/foo/bar?foo=fooVal#fragment',
            $authorizeToken->getAfterUrl()
        );
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|StorageInterface
     */
    protected function createStorageMock()
    {
        return $this->getMock('Payum\Core\Storage\StorageInterface');
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|StorageRegistryInterface
     */
    protected function createStorageRegistryMock()
    {
        return $this->getMock('Payum\Core\Registry\StorageRegistryInterface');
    }
}
