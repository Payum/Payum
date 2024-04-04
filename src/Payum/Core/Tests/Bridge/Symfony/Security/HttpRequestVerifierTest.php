<?php
namespace Payum\Core\Tests\Bridge\Symfony\Security;

use Payum\Core\Bridge\Symfony\Security\HttpRequestVerifier;
use Payum\Core\Model\Token;
use Payum\Core\Storage\StorageInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;

class HttpRequestVerifierTest extends TestCase
{
    public function testShouldImplementHttpRequestVerifierInterface()
    {
        $rc = new \ReflectionClass('Payum\Core\Bridge\Symfony\Security\HttpRequestVerifier');

        $this->assertTrue($rc->implementsInterface('Payum\Core\Security\HttpRequestVerifierInterface'));
    }

    public function testThrowIfNotSymfonyRequestGivenOnVerify()
    {
        $this->expectException(\Payum\Core\Exception\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid request given. Expected Symfony\Component\HttpFoundation\Request but it is stdClass');
        $verifier = new HttpRequestVerifier($this->createStorageMock());

        $verifier->verify(new \stdClass());
    }

    public function testThrowIfRequestNotContainTokenParameterOnVerify()
    {
        $this->expectException(\Symfony\Component\HttpKernel\Exception\NotFoundHttpException::class);
        $this->expectExceptionMessage('Token parameter not set in request');
        $verifier = new HttpRequestVerifier($this->createStorageMock());

        $verifier->verify(Request::create('/'));
    }

    public function testThrowIfStorageCouldNotFindTokenByGivenHashOnVerify()
    {
        $this->expectException(\Symfony\Component\HttpKernel\Exception\NotFoundHttpException::class);
        $this->expectExceptionMessage('A token with hash `invalidHash` could not be found.');
        $invalidHash = 'invalidHash';

        $storageMock = $this->createStorageMock();
        $storageMock
            ->expects($this->once())
            ->method('find')
            ->with($invalidHash)
            ->willReturn(null)
        ;

        $request = Request::create('/');
        $request->attributes->set('payum_token', $invalidHash);

        $verifier = new HttpRequestVerifier($storageMock);

        $verifier->verify($request);
    }

    public function testThrowIfTargetUrlPathNotMatchServerRequestUriPathOnVerify()
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
            ->willReturn($token)
        ;

        $request = Request::create('http://target.com/bar');
        $request->attributes->set('payum_token', 'theHash');

        $verifier = new HttpRequestVerifier($storageMock);

        $verifier->verify($request);
    }

    public function testShouldReturnExpectedTokenIfAllCheckPassedOnVerify()
    {
        $expectedToken = new Token();
        $expectedToken->setHash('theHash');
        $expectedToken->setTargetUrl('http://target.com/foo');

        $storageMock = $this->createStorageMock();
        $storageMock
            ->expects($this->once())
            ->method('find')
            ->with('theHash')
            ->willReturn($expectedToken)
        ;

        $request = Request::create('http://target.com/foo');
        $request->attributes->set('payum_token', 'theHash');

        $verifier = new HttpRequestVerifier($storageMock);

        $actualToken = $verifier->verify($request);

        $this->assertSame($expectedToken, $actualToken);
    }

    public function testShouldReturnExpectedTokenIfAllCheckPassedOnVerifyAndHashSetToQuery()
    {
        $expectedToken = new Token();
        $expectedToken->setHash('theHash');
        $expectedToken->setTargetUrl('http://target.com/foo');

        $storageMock = $this->createStorageMock();
        $storageMock
            ->expects($this->once())
            ->method('find')
            ->with('theHash')
            ->willReturn($expectedToken)
        ;

        $request = Request::create('http://target.com/foo');
        $request->query->set('payum_token', 'theHash');

        $verifier = new HttpRequestVerifier($storageMock);

        $actualToken = $verifier->verify($request);

        $this->assertSame($expectedToken, $actualToken);
    }

    /**
     * @group legacy
     */
    public function testShouldReturnExpectedTokenIfTokenSetToRequestAttribute()
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
     * @group legacy
     */
    public function testShouldReturnExpectedTokenIfTokenSetToEncodedRequestAttribute()
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
     * @group legacy
     */
    public function testShouldNotMatchUriIfTokenSetToRequestAttribute()
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

    public function testShouldCallStorageDeleteModelMethodOnInvalidate()
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
     * @return \PHPUnit_Framework_MockObject_MockObject|StorageInterface
     */
    protected function createStorageMock()
    {
        return $this->createMock('Payum\Core\Storage\StorageInterface');
    }
}
