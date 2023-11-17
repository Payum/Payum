<?php

namespace Payum\Core\Tests\Bridge\Symfony\Security;

use Payum\Core\Bridge\Symfony\Security\HttpRequestVerifier;
use Payum\Core\Exception\InvalidArgumentException;
use Payum\Core\Model\Token;
use Payum\Core\Security\HttpRequestVerifierInterface;
use Payum\Core\Storage\StorageInterface;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use stdClass;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class HttpRequestVerifierTest extends TestCase
{
    public function testShouldImplementHttpRequestVerifierInterface(): void
    {
        $rc = new ReflectionClass(HttpRequestVerifier::class);

        $this->assertTrue($rc->implementsInterface(HttpRequestVerifierInterface::class));
    }

    public function testThrowIfNotSymfonyRequestGivenOnVerify(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid request given. Expected Symfony\Component\HttpFoundation\Request but it is stdClass');
        $verifier = new HttpRequestVerifier($this->createStorageMock());

        $verifier->verify(new stdClass());
    }

    public function testThrowIfRequestNotContainTokenParameterOnVerify(): void
    {
        $this->expectException(NotFoundHttpException::class);
        $this->expectExceptionMessage('Token parameter not set in request');
        $verifier = new HttpRequestVerifier($this->createStorageMock());

        $verifier->verify(Request::create('/'));
    }

    public function testThrowIfStorageCouldNotFindTokenByGivenHashOnVerify(): void
    {
        $this->expectException(NotFoundHttpException::class);
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

    public function testThrowIfTargetUrlPathNotMatchServerRequestUriPathOnVerify(): void
    {
        $this->expectException(HttpException::class);
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

    public function testShouldReturnExpectedTokenIfAllCheckPassedOnVerify(): void
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

    public function testShouldReturnExpectedTokenIfAllCheckPassedOnVerifyAndHashSetToQuery(): void
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
    public function testShouldReturnExpectedTokenIfTokenSetToRequestAttribute(): void
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
    public function testShouldReturnExpectedTokenIfTokenSetToEncodedRequestAttribute(): void
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
    public function testShouldNotMatchUriIfTokenSetToRequestAttribute(): void
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

    public function testShouldCallStorageDeleteModelMethodOnInvalidate(): void
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
        return $this->createMock(StorageInterface::class);
    }
}
