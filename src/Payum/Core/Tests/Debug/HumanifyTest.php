<?php

namespace Payum\Core\Tests\Debug;

use Payum\Core\Debug\Humanify;
use Payum\Core\Reply\HttpRedirect;
use Payum\Core\Request\Capture;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

class HumanifyTest extends TestCase
{
    public function testShouldBeAbstract(): void
    {
        $rc = new ReflectionClass(Humanify::class);

        $this->assertTrue($rc->isAbstract());
    }

    public function testCouldNotBeInstantiable(): void
    {
        $rc = new ReflectionClass(Humanify::class);

        $this->assertFalse($rc->isInstantiable());
    }

    public function testShouldReturnObjectClassOnValueIfObjectPassed(): void
    {
        $this->assertSame('HumanifyTest', Humanify::value($this));
    }

    public function testShouldReturnObjectShortClassOnValueIfObjectPassedAndShortClassFlagSetTrue(): void
    {
        $this->assertSame('HumanifyTest', Humanify::value($this, true));
    }

    public function testShouldReturnObjectClassOnValueIfObjectPassedAndShortClassFlagSetFalse(): void
    {
        $this->assertSame(self::class, Humanify::value($this, false));
    }

    public function testShouldReturnValueTypeIfNotObjectValueGivenOnValue(): void
    {
        $this->assertSame('string', Humanify::value('foo'));
    }

    public function testShouldReturnRequestTypeIfRequestNotObjectOnRequest(): void
    {
        $this->assertSame('string', Humanify::request('foo'));
    }

    public function testShouldReturnRequestShortClassIfRequestObjectOnRequest(): void
    {
        $this->assertSame('HumanifyTest', Humanify::request($this));
    }

    public function testShouldReturnRequestShortClassAndModelIfRequestImplementsModelRequestInterfaceOnRequest(): void
    {
        $request = new Capture($this);

        $this->assertSame('Capture{model: HumanifyTest}', Humanify::request($request));
    }

    public function testShouldReturnReplyShortClassAndUrlIfHttpRedirectReplyOnRequest(): void
    {
        $request = new HttpRedirect('http://example.com/foo');

        $this->assertSame('HttpRedirect{url: http://example.com/foo}', Humanify::request($request));
    }
}
