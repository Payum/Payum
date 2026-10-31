<?php

declare(strict_types=1);

namespace Payum\Core\Tests\Security;

use Nyholm\Psr7\ServerRequest;
use Payum\Core\Exception\InvalidArgumentException;
use Payum\Core\Model\Token;
use Payum\Core\Security\HttpRequestVerifier;
use Payum\Core\Security\HttpRequestVerifierInterface;
use Payum\Core\Security\TokenInterface;
use Payum\Core\Storage\StorageInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class HttpRequestVerifierTest extends TestCase
{
    protected function setUp(): void
    {
        // Verification must not depend on any of these; phpunit.xml.dist backs globals up and restores them.
        $_SERVER = [];
        $_GET = [];
        $_POST = [];
        $_REQUEST = [];
    }

    public function testShouldImplementHttpRequestVerifierInterface(): void
    {
        $this->assertInstanceOf(HttpRequestVerifierInterface::class, new HttpRequestVerifier($this->createStorageMock()));
    }

    public function testShouldFindTheTokenFromTheQueryAndCheckItAgainstTheRequestUri(): void
    {
        $token = $this->token('theHash', 'https://shop.example/capture/theHash');

        $verifier = new HttpRequestVerifier($this->storageFinding('theHash', $token));

        $request = (new ServerRequest('GET', 'https://shop.example/capture/theHash?payum_token=theHash'))
            ->withQueryParams([
                'payum_token' => 'theHash',
            ]);

        $this->assertSame($token, $verifier->verify($request));
    }

    public function testShouldFindTheTokenInAPostedBody(): void
    {
        $token = $this->token('theHash', 'https://shop.example/notify');

        $verifier = new HttpRequestVerifier($this->storageFinding('theHash', $token));

        $request = (new ServerRequest('POST', 'https://shop.example/notify'))
            ->withParsedBody([
                'payum_token' => 'theHash',
            ]);

        $this->assertSame($token, $verifier->verify($request));
    }

    public function testShouldFindTheTokenInARequestAttribute(): void
    {
        $token = $this->token('theHash', 'https://shop.example/capture/theHash');

        $verifier = new HttpRequestVerifier($this->storageFinding('theHash', $token));

        $request = (new ServerRequest('GET', 'https://shop.example/capture/theHash'))
            ->withAttribute('payum_token', 'theHash');

        $this->assertSame($token, $verifier->verify($request));
    }

    public function testShouldMatchAPercentEncodedPath(): void
    {
        $token = $this->token('theHash', 'https://shop.example/_SsYp0j9YWCZfC0qpxCK58s0kaSBXVTYVDecuCqo6_w');

        $verifier = new HttpRequestVerifier($this->storageFinding('theHash', $token));

        $request = (new ServerRequest('GET', 'https://shop.example/%5FSsYp0j9YWCZfC0qpxCK58s0kaSBXVTYVDecuCqo6%5Fw'))
            ->withQueryParams([
                'payum_token' => 'theHash',
            ]);

        $this->assertSame($token, $verifier->verify($request));
    }

    public function testThrowIfTheRequestPathDoesNotMatchTheTokenTargetUrl(): void
    {
        $token = $this->token('theHash', 'https://shop.example/foo');

        $verifier = new HttpRequestVerifier($this->storageFinding('theHash', $token));

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The current url https://shop.example/bar?payum_token=theHash not match target url https://shop.example/foo set in the token.');

        $verifier->verify(
            (new ServerRequest('GET', 'https://shop.example/bar?payum_token=theHash'))->withQueryParams([
                'payum_token' => 'theHash',
            ])
        );
    }

    public function testThrowIfGivenAnythingButAServerRequest(): void
    {
        $verifier = new HttpRequestVerifier($this->createStorageMock());

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid request given. Expected Psr\Http\Message\ServerRequestInterface but it is array.');

        $verifier->verify([
            'payum_token' => 'theHash',
        ]);
    }

    public function testThrowIfTheRequestCarriesNoToken(): void
    {
        $verifier = new HttpRequestVerifier($this->createStorageMock());

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Token parameter `payum_token` was not found in in the http request.');

        $verifier->verify(new ServerRequest('GET', 'https://shop.example/capture'));
    }

    public function testThrowIfNoTokenIsStoredUnderTheHash(): void
    {
        $verifier = new HttpRequestVerifier($this->storageFinding('invalidHash', null));

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('A token with hash `invalidHash` could not be found.');

        $verifier->verify((new ServerRequest('GET', 'https://shop.example/capture'))->withQueryParams([
            'payum_token' => 'invalidHash',
        ]));
    }

    public function testShouldReturnATokenObjectCarriedByTheRequestWithoutChecks(): void
    {
        $token = new Token();

        $storage = $this->createStorageMock();
        $storage->expects($this->never())->method('find');

        $verifier = new HttpRequestVerifier($storage);

        $this->assertSame($token, $verifier->verify(
            (new ServerRequest('GET', 'https://shop.example/anywhere'))->withAttribute('payum_token', $token)
        ));
    }

    public function testShouldAllowCustomizingTheTokenParameter(): void
    {
        $token = $this->token('theHash', 'https://shop.example/capture');

        $verifier = new HttpRequestVerifier($this->storageFinding('theHash', $token), 'custom_token');

        $this->assertSame($token, $verifier->verify(
            (new ServerRequest('GET', 'https://shop.example/capture'))->withQueryParams([
                'custom_token' => 'theHash',
            ])
        ));
    }

    public function testShouldDeleteTheTokenOnInvalidate(): void
    {
        $token = new Token();

        $storage = $this->createStorageMock();
        $storage->expects($this->once())->method('delete')->with($this->identicalTo($token));

        (new HttpRequestVerifier($storage))->invalidate($token);
    }

    private function token(string $hash, string $targetUrl): Token
    {
        $token = new Token();
        $token->setHash($hash);
        $token->setTargetUrl($targetUrl);

        return $token;
    }

    /**
     * @return StorageInterface<TokenInterface>&MockObject
     */
    private function storageFinding(string $hash, ?TokenInterface $token): StorageInterface
    {
        $storage = $this->createStorageMock();
        $storage->expects($this->once())->method('find')->with($hash)->willReturn($token);

        return $storage;
    }

    /**
     * @return StorageInterface<TokenInterface>&MockObject
     */
    private function createStorageMock(): StorageInterface
    {
        return $this->createMock(StorageInterface::class);
    }
}
