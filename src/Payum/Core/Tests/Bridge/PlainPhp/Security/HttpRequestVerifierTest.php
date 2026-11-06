<?php

declare(strict_types=1);

namespace Payum\Core\Tests\Bridge\PlainPhp\Security;

use Nyholm\Psr7\ServerRequest;
use Payum\Core\Bridge\PlainPhp\Security\HttpRequestVerifier;
use Payum\Core\Exception\InvalidArgumentException;
use Payum\Core\Model\Token;
use Payum\Core\Security\HttpRequestVerifierInterface;
use Payum\Core\Storage\StorageInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use Symfony\Component\HttpFoundation\Request;

final class HttpRequestVerifierTest extends TestCase
{
    public function testShouldImplementHttpRequestVerifierInterface(): void
    {
        $rc = new ReflectionClass(HttpRequestVerifier::class);

        $this->assertTrue($rc->implementsInterface(HttpRequestVerifierInterface::class));
    }

    public function testThrowIfRequestIsNotArrayOnVerify(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid request given. In most cases you have to pass $_REQUEST array.');
        $verifier = new HttpRequestVerifier($this->createStorageMock());

        $verifier->verify('not array');
    }

    /**
     * @group legacy
     */
    public function testThrowIfRequestNotContainTokenParameterOnVerify(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Token parameter `payum_token` was not found in in the http request.');
        $verifier = new HttpRequestVerifier($this->createStorageMock());

        $verifier->verify([]);
    }

    /**
     * @group legacy
     */
    public function testThrowIfStorageCouldNotFindTokenByGivenHashOnVerify(): void
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

    /**
     * @group legacy
     */
    public function testThrowIfTargetUrlPathNotMatchServerRequestUriPathOnVerify(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The current url https://target.com/bar not match target url https://target.com/foo set in the token.');
        $_SERVER['REQUEST_URI'] = 'https://target.com/bar';

        $token = new Token();
        $token->setHash('theHash');
        $token->setTargetUrl('https://target.com/foo');

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

    /**
     * @group legacy
     */
    public function testShouldReturnExpectedTokenIfAllCheckPassedOnVerify(): void
    {
        $_SERVER['REQUEST_URI'] = 'https://target.com/foo';

        $expectedToken = new Token();
        $expectedToken->setHash('theHash');
        $expectedToken->setTargetUrl('https://target.com/foo');

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

    /**
     * @group legacy
     */
    public function testShouldReturnExpectedTokenIfAllEncodedCheckPassedOnVerify(): void
    {
        $_SERVER['REQUEST_URI'] = 'https://target.com/%5FSsYp0j9YWCZfC0qpxCK58s0kaSBXVTYVDecuCqo6%5Fw';

        $expectedToken = new Token();
        $expectedToken->setHash('theHash');
        $expectedToken->setTargetUrl('https://target.com/_SsYp0j9YWCZfC0qpxCK58s0kaSBXVTYVDecuCqo6_w');

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

    /**
     * @group legacy
     */
    public function testShouldReturnTokenObjectSetToRequestGlobalArrayWithoutChecks(): void
    {
        $expectedToken = new Token();

        $verifier = new HttpRequestVerifier($this->createStorageMock());

        $actualToken = $verifier->verify([
            'payum_token' => $expectedToken,
        ]);

        $this->assertSame($expectedToken, $actualToken);
    }

    /**
     * @group legacy
     */
    public function testShouldAllowCustomizeTokenParameterInConstructor(): void
    {
        $expectedToken = new Token();

        $verifier = new HttpRequestVerifier($this->createStorageMock(), 'custom_token');

        $actualToken = $verifier->verify([
            'custom_token' => $expectedToken,
        ]);

        $this->assertSame($expectedToken, $actualToken);
    }

    public function testShouldVerifyAServerRequestWithoutReadingGlobals(): void
    {
        $_SERVER['REQUEST_URI'] = '/somewhere-else';

        $token = new Token();
        $token->setHash('theHash');
        $token->setTargetUrl('https://target.com/foo');

        $storageMock = $this->createStorageMock();
        $storageMock->expects($this->once())->method('find')->with('theHash')->willReturn($token);

        $verifier = new HttpRequestVerifier($storageMock);

        $deprecations = $this->collectDeprecations(fn () => $this->assertSame($token, $verifier->verify(
            (new ServerRequest('GET', 'https://target.com/foo'))->withQueryParams([
                'payum_token' => 'theHash',
            ])
        )));

        $this->assertSame([], $deprecations);
    }

    public function testShouldCheckAnHttpFoundationRequestAgainstItsOwnUri(): void
    {
        $_SERVER['REQUEST_URI'] = '/somewhere-else';

        $token = new Token();
        $token->setHash('theHash');
        $token->setTargetUrl('https://target.com/foo');

        $storageMock = $this->createStorageMock();
        $storageMock->expects($this->once())->method('find')->with('theHash')->willReturn($token);

        $verifier = new HttpRequestVerifier($storageMock);

        $deprecations = $this->collectDeprecations(fn () => $this->assertSame($token, $verifier->verify(
            Request::create('https://target.com/foo', 'GET', [
                'payum_token' => 'theHash',
            ])
        )));

        $this->assertSame([], $deprecations);
    }

    public function testShouldVerifyAScriptRequestedDirectly(): void
    {
        $token = new Token();
        $token->setHash('theHash');
        $token->setTargetUrl('http://target.com/capture.php');

        $storageMock = $this->createStorageMock();
        $storageMock->expects($this->once())->method('find')->with('theHash')->willReturn($token);

        $verifier = new HttpRequestVerifier($storageMock);

        // What a SAPI sets for http://target.com/capture.php?payum_token=theHash
        $request = new Request([
            'payum_token' => 'theHash',
        ], [], [], [], [], [
            'HTTP_HOST' => 'target.com',
            'SCRIPT_NAME' => '/capture.php',
            'SCRIPT_FILENAME' => '/var/www/capture.php',
            'PHP_SELF' => '/capture.php',
            'REQUEST_URI' => '/capture.php?payum_token=theHash',
            'QUERY_STRING' => 'payum_token=theHash',
        ]);

        $this->assertSame($token, $verifier->verify($request));
    }

    public function testShouldDeprecateVerifyingAnArray(): void
    {
        $verifier = new HttpRequestVerifier($this->createStorageMock());

        $deprecations = $this->collectDeprecations(fn () => $verifier->verify([
            'payum_token' => new Token(),
        ]));

        $this->assertSame([
            'Since payum/core 2.0.0: Passing an array to Payum\\Core\\Bridge\\PlainPhp\\Security\\HttpRequestVerifier::verify() is deprecated and removed in 3.0, because it reads the URL from $_SERVER. Pass a Psr\\Http\\Message\\ServerRequestInterface instead.',
        ], $deprecations);
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
     * @return MockObject|StorageInterface<object>
     */
    protected function createStorageMock(): StorageInterface | MockObject
    {
        return $this->createMock(StorageInterface::class);
    }

    /**
     * @return list<string>
     */
    private function collectDeprecations(callable $call): array
    {
        $deprecations = [];
        set_error_handler(static function (int $level, string $message) use (&$deprecations): bool {
            $deprecations[] = $message;

            return true;
        }, E_USER_DEPRECATED);

        try {
            $call();
        } finally {
            restore_error_handler();
        }

        return $deprecations;
    }
}
