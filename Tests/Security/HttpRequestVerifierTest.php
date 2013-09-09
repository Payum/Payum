<?php
namespace Payum\Bundle\PayumBundle\Security;

use Payum\Model\Token;
use Payum\Storage\StorageInterface;
use Symfony\Component\HttpFoundation\Request;

class HttpRequestVerifierTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldImplementHttpRequestVerifierInterface()
    {
        $rc = new \ReflectionClass('Payum\Bundle\PayumBundle\Security\HttpRequestVerifier');

        $this->assertTrue($rc->implementsInterface('Payum\Security\HttpRequestVerifierInterface'));
    }

    /**
     * @test
     */
    public function couldBeConstructedWithTokenStorageAsFirstArgument()
    {
        new HttpRequestVerifier($this->createStorageMock());
    }

    /**
     * @test
     *
     * @expectedException \Payum\Exception\InvalidArgumentException
     * @expectedExceptionMessage Invalid request given. Expected Symfony\Component\HttpFoundation\Request but it is stdClass
     */
    public function throwIfNotSymfonyRequestGivenOnVerify()
    {
        $verifier = new HttpRequestVerifier($this->createStorageMock());

        $verifier->verify(new \stdClass);
    }

    /**
     * @test
     *
     * @expectedException \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     * @expectedExceptionMessage Token parameter not set in request
     */
    public function throwIfRequestNotContainTokenParameterOnVerify()
    {
        $verifier = new HttpRequestVerifier($this->createStorageMock());

        $verifier->verify(Request::create('/'));
    }

    /**
     * @test
     *
     * @expectedException \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     * @expectedExceptionMessage A token with hash `invalidHash` could not be found.
     */
    public function throwIfStorageCouldNotFindTokenByGivenHashOnVerify()
    {
        $invalidHash = 'invalidHash';

        $storageMock = $this->createStorageMock();
        $storageMock
            ->expects($this->once())
            ->method('findModelById')
            ->with($invalidHash)
            ->will($this->returnValue(null))
        ;

        $request = Request::create('/');
        $request->attributes->set('payum_token', $invalidHash);

        $verifier = new HttpRequestVerifier($storageMock);

        $verifier->verify($request);
    }

    /**
     * @test
     *
     * @expectedException \Symfony\Component\HttpKernel\Exception\HttpException
     * @expectedExceptionMessage The current url http://target.com/bar not match target url http://target.com/foo set in the token.
     */
    public function throwIfTargetUrlPathNotMatchServerRequestUriPathOnVerify()
    {
        $token = new \Payum\Model\Token;
        $token->setHash('theHash');
        $token->setTargetUrl('http://target.com/foo');

        $storageMock = $this->createStorageMock();
        $storageMock
            ->expects($this->once())
            ->method('findModelById')
            ->with('theHash')
            ->will($this->returnValue($token))
        ;

        $request = Request::create('http://target.com/bar');
        $request->attributes->set('payum_token', 'theHash');

        $verifier = new HttpRequestVerifier($storageMock);

        $verifier->verify($request);
    }

    /**
     * @test
     */
    public function shouldReturnExpectedTokenIfAllCheckPassedOnVerify()
    {
        $expectedToken = new Token;
        $expectedToken->setHash('theHash');
        $expectedToken->setTargetUrl('http://target.com/foo');

        $storageMock = $this->createStorageMock();
        $storageMock
            ->expects($this->once())
            ->method('findModelById')
            ->with('theHash')
            ->will($this->returnValue($expectedToken))
        ;

        $request = Request::create('http://target.com/foo');
        $request->attributes->set('payum_token', 'theHash');

        $verifier = new HttpRequestVerifier($storageMock);

        $actualToken = $verifier->verify($request);

        $this->assertSame($expectedToken, $actualToken);
    }

    /**
     * @test
     */
    public function shouldReturnExpectedTokenIfAllCheckPassedOnVerifyAndHashSetToQuery()
    {
        $expectedToken = new Token;
        $expectedToken->setHash('theHash');
        $expectedToken->setTargetUrl('http://target.com/foo');

        $storageMock = $this->createStorageMock();
        $storageMock
            ->expects($this->once())
            ->method('findModelById')
            ->with('theHash')
            ->will($this->returnValue($expectedToken))
        ;

        $request = Request::create('http://target.com/foo');
        $request->query->set('payum_token', 'theHash');

        $verifier = new HttpRequestVerifier($storageMock);

        $actualToken = $verifier->verify($request);

        $this->assertSame($expectedToken, $actualToken);
    }

    /**
     * @test
     */
    public function shouldReturnExpectedTokenIfTokenSetToRequestAttribute()
    {
        $expectedToken = new \Payum\Model\Token;
        $expectedToken->setHash('theHash');
        $expectedToken->setTargetUrl('http://target.com/foo');

        $storageMock = $this->createStorageMock();
        $storageMock
            ->expects($this->never())
            ->method('findModelById')
        ;

        $request = Request::create('http://target.com/foo');
        $request->query->set('payum_token', $expectedToken);

        $verifier = new HttpRequestVerifier($storageMock);

        $actualToken = $verifier->verify($request);

        $this->assertSame($expectedToken, $actualToken);
    }

    /**
     * @test
     */
    public function shouldNotMatchUriIfTokenSetToRequestAttribute()
    {
        $expectedToken = new \Payum\Model\Token;
        $expectedToken->setHash('theHash');
        $expectedToken->setTargetUrl('http://target.com/bar');

        $storageMock = $this->createStorageMock();
        $storageMock
            ->expects($this->never())
            ->method('findModelById')
        ;

        $request = Request::create('http://target.com/foo');
        $request->query->set('payum_token', $expectedToken);

        $verifier = new HttpRequestVerifier($storageMock);

        $actualToken = $verifier->verify($request);

        $this->assertSame($expectedToken, $actualToken);
    }

    /**
     * @test
     */
    public function shouldCallStorageDeleteModelMethodOnInvalidate()
    {
        $token = new \Payum\Model\Token;

        $storageMock = $this->createStorageMock();
        $storageMock
            ->expects($this->once())
            ->method('deleteModel')
            ->with($this->identicalTo($token))
        ;

        $verifier = new HttpRequestVerifier($storageMock);

        $verifier->invalidate($token);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|StorageInterface
     */
    protected function createStorageMock()
    {
        return $this->getMock('Payum\Storage\StorageInterface');
    }
}