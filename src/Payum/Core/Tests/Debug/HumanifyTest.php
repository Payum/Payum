<?php
namespace Payum\Core\Tests\Debug;

use Payum\Core\Debug\Humanify;
use Payum\Core\Request\Capture;
use Payum\Core\Reply\HttpRedirect;
use PHPUnit\Framework\TestCase;

class HumanifyTest extends TestCase
{
    /**
     * @test
     */
    public function shouldBeAbstract(): void
    {
        $rc = new \ReflectionClass('Payum\Core\Debug\Humanify');

        $this->assertTrue($rc->isAbstract());
    }

    /**
     * @test
     */
    public function couldNotBeInstantiable(): void
    {
        $rc = new \ReflectionClass('Payum\Core\Debug\Humanify');

        $this->assertFalse($rc->isInstantiable());
    }

    /**
     * @test
     */
    public function shouldReturnObjectClassOnValueIfObjectPassed(): void
    {
        $this->assertEquals('HumanifyTest', Humanify::value($this));
    }

    /**
     * @test
     */
    public function shouldReturnObjectShortClassOnValueIfObjectPassedAndShortClassFlagSetTrue(): void
    {
        $this->assertEquals('HumanifyTest', Humanify::value($this, true));
    }

    /**
     * @test
     */
    public function shouldReturnObjectClassOnValueIfObjectPassedAndShortClassFlagSetFalse(): void
    {
        $this->assertEquals(__CLASS__, Humanify::value($this, false));
    }

    /**
     * @test
     */
    public function shouldReturnValueTypeIfNotObjectValueGivenOnValue(): void
    {
        $this->assertEquals('string', Humanify::value('foo'));
    }

    /**
     * @test
     */
    public function shouldReturnRequestTypeIfRequestNotObjectOnRequest(): void
    {
        $this->assertEquals('string', Humanify::request('foo'));
    }

    /**
     * @test
     */
    public function shouldReturnRequestShortClassIfRequestObjectOnRequest(): void
    {
        $this->assertEquals('HumanifyTest', Humanify::request($this));
    }

    /**
     * @test
     */
    public function shouldReturnRequestShortClassAndModelIfRequestImplementsModelRequestInterfaceOnRequest(): void
    {
        $request = new Capture($this);

        $this->assertEquals('Capture{model: HumanifyTest}', Humanify::request($request));
    }

    /**
     * @test
     */
    public function shouldReturnReplyShortClassAndUrlIfHttpRedirectReplyOnRequest(): void
    {
        $request = new HttpRedirect('http://example.com/foo');

        $this->assertEquals('HttpRedirect{url: http://example.com/foo}', Humanify::request($request));
    }
}
