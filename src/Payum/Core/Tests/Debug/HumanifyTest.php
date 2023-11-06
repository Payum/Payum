<?php
namespace Payum\Core\Tests\Debug;

use Payum\Core\Debug\Humanify;
use Payum\Core\Request\Capture;
use Payum\Core\Reply\HttpRedirect;
use PHPUnit\Framework\TestCase;

class HumanifyTest extends TestCase
{
    public function testShouldBeAbstract()
    {
        $rc = new \ReflectionClass('Payum\Core\Debug\Humanify');

        $this->assertTrue($rc->isAbstract());
    }

    public function testCouldNotBeInstantiable()
    {
        $rc = new \ReflectionClass('Payum\Core\Debug\Humanify');

        $this->assertFalse($rc->isInstantiable());
    }

    public function testShouldReturnObjectClassOnValueIfObjectPassed()
    {
        $this->assertSame('HumanifyTest', Humanify::value($this));
    }

    public function testShouldReturnObjectShortClassOnValueIfObjectPassedAndShortClassFlagSetTrue()
    {
        $this->assertSame('HumanifyTest', Humanify::value($this, true));
    }

    public function testShouldReturnObjectClassOnValueIfObjectPassedAndShortClassFlagSetFalse()
    {
        $this->assertSame(self::class, Humanify::value($this, false));
    }

    public function testShouldReturnValueTypeIfNotObjectValueGivenOnValue()
    {
        $this->assertSame('string', Humanify::value('foo'));
    }

    public function testShouldReturnRequestTypeIfRequestNotObjectOnRequest()
    {
        $this->assertSame('string', Humanify::request('foo'));
    }

    public function testShouldReturnRequestShortClassIfRequestObjectOnRequest()
    {
        $this->assertSame('HumanifyTest', Humanify::request($this));
    }

    public function testShouldReturnRequestShortClassAndModelIfRequestImplementsModelRequestInterfaceOnRequest()
    {
        $request = new Capture($this);

        $this->assertSame('Capture{model: HumanifyTest}', Humanify::request($request));
    }

    public function testShouldReturnReplyShortClassAndUrlIfHttpRedirectReplyOnRequest()
    {
        $request = new HttpRedirect('http://example.com/foo');

        $this->assertSame('HttpRedirect{url: http://example.com/foo}', Humanify::request($request));
    }
}
