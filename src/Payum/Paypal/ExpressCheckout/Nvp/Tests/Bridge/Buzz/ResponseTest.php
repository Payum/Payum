<?php
namespace Payum\Paypal\ExpressCheckout\Nvp\Tests\Bridge\Buzz;

use Payum\Paypal\ExpressCheckout\Nvp\Bridge\Buzz\Response;

class ResponseTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldBeSubClassOfBuzzResponse()
    {
        $rc = new \ReflectionClass('Payum\Paypal\ExpressCheckout\Nvp\Bridge\Buzz\Response');
        
        $this->assertTrue($rc->isSubclassOf('Buzz\Message\Response'));
    }

    /**
     * @test
     */
    public function shouldImplementArrayAccessInterface()
    {
        $rc = new \ReflectionClass('Payum\Paypal\ExpressCheckout\Nvp\Bridge\Buzz\Response');
        
        $this->assertTrue($rc->implementsInterface('ArrayAccess'));
    }

    /**
     * @test
     */
    public function shouldImplementIteratorAggregateInterface()
    {
        $rc = new \ReflectionClass('Payum\Paypal\ExpressCheckout\Nvp\Bridge\Buzz\Response');

        $this->assertTrue($rc->implementsInterface('IteratorAggregate'));
    }

    /**
     * @test
     */
    public function couldBeConstructedWithoutAnyArguments()
    {
        new Response;
    }

    /**
     * @test
     */
    public function shouldAllowSetValue()
    {
        $response = new Response;
        $response['foo'] = 'foo';
    }

    /**
     * @test
     */
    public function shouldAllowGetPreviouslySetValue()
    {
        $expectedValue = 'foo';
        
        $response = new Response;
        $response['foo'] = $expectedValue;
        
        $this->assertEquals($expectedValue, $response['foo']);
    }

    /**
     * @test
     */
    public function shouldGetNullIfValueNotSet()
    {
        $response = new Response;
        
        $this->assertNull($response['foo']);
    }

    /**
     * @test
     */
    public function shouldParseContentToNvp()
    {
        $response = new Response;
        $response->setContent('foo=foo&bar=bar');

        $this->assertEquals('foo', $response['foo']);
        $this->assertEquals('bar', $response['bar']);
    }

    /**
     * @test
     */
    public function shouldUrlDecodeWhileParseContentToNvp()
    {
        $response = new Response;
        $response->setContent('foo=%5C_%3F%26+');

        $this->assertEquals('\_?& ', $response['foo']);
    }

    /**
     * @test
     */
    public function shouldIssetTrueIfHasValue()
    {
        $response = new Response;
        $response['foo'] = 'foo';

        $this->assertTrue(isset($response['foo']));
    }

    /**
     * @test
     */
    public function shouldIssetFalseIfNotHasValue()
    {
        $response = new Response;

        $this->assertFalse(isset($response['foo']));
    }

    /**
     * @test
     */
    public function shouldAllowUnsetValue()
    {
        $response = new Response;
        $response['foo'] = 'foo';

        unset($response['foo']);
        
        $this->assertNull($response['foo']);
    }
}