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
    public function shouldBeAbstract()
    {
        $rc = new \ReflectionClass('Payum\Core\Debug\Humanify');

        $this->assertTrue($rc->isAbstract());
    }

    /**
     * @test
     */
    public function couldNotBeInstantiable()
    {
        $rc = new \ReflectionClass('Payum\Core\Debug\Humanify');

        $this->assertFalse($rc->isInstantiable());
    }

    /**
     * @test
     */
    public function shouldReturnObjectClassOnValueIfObjectPassed()
    {
        $this->assertSame('HumanifyTest', Humanify::value($this));
    }

    /**
     * @test
     */
    public function shouldReturnObjectShortClassOnValueIfObjectPassedAndShortClassFlagSetTrue()
    {
        $this->assertSame('HumanifyTest', Humanify::value($this, true));
    }

    /**
     * @test
     */
    public function shouldReturnObjectClassOnValueIfObjectPassedAndShortClassFlagSetFalse()
    {
        $this->assertSame(__CLASS__, Humanify::value($this, false));
    }

    /**
     * @test
     */
    public function shouldReturnValueTypeIfNotObjectValueGivenOnValue()
    {
        $this->assertSame('string', Humanify::value('foo'));
    }

    /**
     * @test
     */
    public function shouldReturnRequestTypeIfRequestNotObjectOnRequest()
    {
        $this->assertSame('string', Humanify::request('foo'));
    }

    /**
     * @test
     */
    public function shouldReturnRequestShortClassIfRequestObjectOnRequest()
    {
        $this->assertSame('HumanifyTest', Humanify::request($this));
    }

    /**
     * @test
     */
    public function shouldReturnRequestShortClassAndModelIfRequestImplementsModelRequestInterfaceOnRequest()
    {
        $request = new Capture($this);

        $this->assertSame('Capture{model: HumanifyTest}', Humanify::request($request));
    }

    /**
     * @test
     */
    public function shouldReturnReplyShortClassAndUrlIfHttpRedirectReplyOnRequest()
    {
        $request = new HttpRedirect('http://example.com/foo');

        $this->assertSame('HttpRedirect{url: http://example.com/foo}', Humanify::request($request));
    }
}
