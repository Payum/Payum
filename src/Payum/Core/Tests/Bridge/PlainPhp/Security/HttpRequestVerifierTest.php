<?php

namespace Payum\Core\Tests\Bridge\PlainPhp\Security;

use Payum\Core\Bridge\PlainPhp\Security\HttpRequestVerifier;
use Payum\Core\Exception\InvalidArgumentException;
use Payum\Core\Model\Token;
use Payum\Core\Security\HttpRequestVerifierInterface;
use Payum\Core\Storage\StorageInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

class HttpRequestVerifierTest extends TestCase
{
    public function testShouldImplementHttpRequestVerifierInterface()
    {
        $rc = new ReflectionClass(HttpRequestVerifier::class);

        $this->assertTrue($rc->implementsInterface(HttpRequestVerifierInterface::class));
    }

    public function testThrowIfRequestIsNotArrayOnVerify()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid request given. In most cases you have to pass $_REQUEST array.');
        $verifier = new HttpRequestVerifier($this->createStorageMock());

        $verifier->verify('not array');
    }

    public function testThrowIfRequestNotContainTokenParameterOnVerify()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Token parameter `payum_token` was not found in in the http request.');
        $verifier = new HttpRequestVerifier($this->createStorageMock());

        $verifier->verify([]);
    }

    public function testThrowIfStorageCouldNotFindTokenByGivenHashOnVerify()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('A token with hash `invalidHash` could not be found.');
        $invalidHash = 'invalidHash';

        $storageMock = $this->createStorageMock();
        $storageMock
            ->expects($this->once())
            ->method('find')
            ->with($invalidHash)
            ->willReturn(null)
        ;

        $verifier = new HttpRequestVerifier($storageMock);

        $verifier->verify([
            'payum_token' => $invalidHash,
        ]);
    }

    public function testThrowIfTargetUrlPathNotMatchServerRequestUriPathOnVerify()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The current url http://target.com/bar not match target url http://target.com/foo set in the token.');
        $_SERVER['REQUEST_URI'] = 'http://target.com/bar';

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

        $verifier = new HttpRequestVerifier($storageMock);

        $verifier->verify([
            'payum_token' => 'theHash',
        ]);
    }

    public function testShouldReturnExpectedTokenIfAllCheckPassedOnVerify()
    {
        $_SERVER['REQUEST_URI'] = 'http://target.com/foo';

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

        $verifier = new HttpRequestVerifier($storageMock);

        $actualToken = $verifier->verify([
            'payum_token' => 'theHash',
        ]);

        $this->assertSame($expectedToken, $actualToken);
    }

    public function testShouldReturnExpectedTokenIfAllEncodedCheckPassedOnVerify()
    {
        $_SERVER['REQUEST_URI'] = 'http://target.com/%5FSsYp0j9YWCZfC0qpxCK58s0kaSBXVTYVDecuCqo6%5Fw';

        $expectedToken = new Token();
        $expectedToken->setHash('theHash');
        $expectedToken->setTargetUrl('http://target.com/_SsYp0j9YWCZfC0qpxCK58s0kaSBXVTYVDecuCqo6_w');

        $storageMock = $this->createStorageMock();
        $storageMock
            ->expects($this->once())
            ->method('find')
            ->with('theHash')
            ->willReturn($expectedToken)
        ;

        $verifier = new HttpRequestVerifier($storageMock);

        $actualToken = $verifier->verify([
            'payum_token' => 'theHash',
        ]);

        $this->assertSame($expectedToken, $actualToken);
    }

    public function testShouldReturnTokenObjectSetToRequestGlobalArrayWithoutChecks()
    {
        $expectedToken = new Token();

        $verifier = new HttpRequestVerifier($this->createStorageMock());

        $actualToken = $verifier->verify([
            'payum_token' => $expectedToken,
        ]);

        $this->assertSame($expectedToken, $actualToken);
    }

    public function testShouldAllowCustomizeTokenParameterInConstructor()
    {
        $expectedToken = new Token();

        $verifier = new HttpRequestVerifier($this->createStorageMock(), 'custom_token');

        $actualToken = $verifier->verify([
            'custom_token' => $expectedToken,
        ]);

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
     * @return MockObject|StorageInterface
     */
    protected function createStorageMock()
    {
        return $this->createMock(StorageInterface::class);
    }
}
