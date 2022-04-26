<?php
namespace Payum\Core\Tests\Bridge\Symfony\Security;

use Payum\Core\Bridge\Symfony\Security\HttpRequestVerifier;
use Payum\Core\Model\Token;
use Payum\Core\Storage\StorageInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;

class HttpRequestVerifierTest extends TestCase
{
    /**
     * @test
     */
    public function shouldImplementHttpRequestVerifierInterface()
    {
        $rc = new \ReflectionClass('Payum\Core\Bridge\Symfony\Security\HttpRequestVerifier');

        $this->assertTrue($rc->implementsInterface('Payum\Core\Security\HttpRequestVerifierInterface'));
    }

    /**
     * @test
     */
    public function throwIfNotSymfonyRequestGivenOnVerify()
    {
        $this->expectException(\Payum\Core\Exception\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid request given. Expected Symfony\Component\HttpFoundation\Request but it is stdClass');
        $verifier = new HttpRequestVerifier($this->createStorageMock());

        $verifier->verify(new \stdClass());
    }

    /**
     * @test
     */
    public function throwIfRequestNotContainTokenParameterOnVerify()
    {
        $this->expectException(\Symfony\Component\HttpKernel\Exception\NotFoundHttpException::class);
        $this->expectExceptionMessage('Token parameter not set in request');
        $verifier = new HttpRequestVerifier($this->createStorageMock());

        $verifier->verify(Request::create('/'));
    }

    /**
     * @test
     */
    public function throwIfStorageCouldNotFindTokenByGivenHashOnVerify()
    {
        $this->expectException(\Symfony\Component\HttpKernel\Exception\NotFoundHttpException::class);
        $this->expectExceptionMessage('A token with hash `invalidHash` could not be found.');
        $invalidHash = 'invalidHash';

        $storageMock = $this->createStorageMock();
        $storageMock
            ->expects($this->once())
            ->method('find')
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
     */
    public function throwIfTargetUrlPathNotMatchServerRequestUriPathOnVerify()
    {
        $this->expectException(\Symfony\Component\HttpKernel\Exception\HttpException::class);
        $this->expectExceptionMessage('The current url http://target.com/bar not match target url http://target.com/foo set in the token.');
        $token = new Token();
        $token->setHash('theHash');
        $token->setTargetUrl('http://target.com/foo');

        $storageMock = $this->createStorageMock();
        $storageMock
            ->expects($this->once())
            ->method('find')
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
        $expectedToken = new Token();
        $expectedToken->setHash('theHash');
        $expectedToken->setTargetUrl('http://target.com/foo');

        $storageMock = $this->createStorageMock();
        $storageMock
            ->expects($this->once())
            ->method('find')
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
        $expectedToken = new Token();
        $expectedToken->setHash('theHash');
        $expectedToken->setTargetUrl('http://target.com/foo');

        $storageMock = $this->createStorageMock();
        $storageMock
            ->expects($this->once())
            ->method('find')
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
     * @group legacy
     */
    public function shouldReturnExpectedTokenIfTokenSetToRequestAttribute()
    {
        $expectedToken = new Token();
        $expectedToken->setHash('theHash');
        $expectedToken->setTargetUrl('http://target.com/foo');

        $storageMock = $this->createStorageMock();
        $storageMock
            ->expects($this->never())
            ->method('find')
        ;

        $request = Request::create('http://target.com/foo');
        $request->query->set('payum_token', $expectedToken);

        $verifier = new HttpRequestVerifier($storageMock);

        $actualToken = $verifier->verify($request);

        $this->assertSame($expectedToken, $actualToken);
    }

    /**
     * @test
     * @group legacy
     */
    public function shouldReturnExpectedTokenIfTokenSetToEncodedRequestAttribute()
    {
        $expectedToken = new Token();
        $expectedToken->setHash('theHash');
        $expectedToken->setTargetUrl('http://target.com/_SsYp0j9YWCZfC0qpxCK58s0kaSBXVTYVDecuCqo6_w');

        $storageMock = $this->createStorageMock();
        $storageMock
            ->expects($this->never())
            ->method('find')
        ;

        $request = Request::create('http://target.com/%5FSsYp0j9YWCZfC0qpxCK58s0kaSBXVTYVDecuCqo6%5Fw');
        $request->query->set('payum_token', $expectedToken);

        $verifier = new HttpRequestVerifier($storageMock);

        $actualToken = $verifier->verify($request);

        $this->assertSame($expectedToken, $actualToken);
    }

    /**
     * @test
     * @group legacy
     */
    public function shouldNotMatchUriIfTokenSetToRequestAttribute()
    {
        $expectedToken = new Token();
        $expectedToken->setHash('theHash');
        $expectedToken->setTargetUrl('http://target.com/bar');

        $storageMock = $this->createStorageMock();
        $storageMock
            ->expects($this->never())
            ->method('find')
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
        $token = new Token();

        $storageMock = $this->createStorageMock();
        $storageMock
            ->expects($this->once())
            ->method('delete')
            ->with($this->identicalTo($token))
        ;

        $verifier = new HttpRequestVerifier($storageMock);

        $verifier->invalidate($token);
    }

    /**
     * @return MockObject|StorageInterface
     */
    protected function createStorageMock()
    {
        return $this->createMock('Payum\Core\Storage\StorageInterface');
    }
}
