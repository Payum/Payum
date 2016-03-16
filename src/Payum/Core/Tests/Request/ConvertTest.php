<?php
namespace Payum\Core\Tests\Request;

use Payum\Core\Request\Convert;
use Payum\Core\Security\TokenInterface;

class ConvertTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function couldBeConstructedWithSourceModelAndTo()
    {
        $request = new Convert($source = new \stdClass(), $to = 'array');

        $this->assertSame($source, $request->getSource());
        $this->assertSame($to, $request->getTo());
        $this->assertNull($request->getToken());
        $this->assertNull($request->getResult());
    }

    /**
     * @test
     */
    public function couldBeConstructedWithSourceModelAndToAndOptionnalToken()
    {
        $request = new Convert($source = new \stdClass(), $to = 'array', $token = $this->getMock(TokenInterface::class));

        $this->assertSame($source, $request->getSource());
        $this->assertSame($to, $request->getTo());
        $this->assertSame($token, $request->getToken());
        $this->assertNull($request->getResult());
    }

    /**
     * @test
     */
    public function shouldAllowGetPreviouslySetResult()
    {
        $request = new Convert(new \stdClass(), 'array');

        $request->setResult($result = new \stdClass());

        $this->assertSame($result, $request->getResult());
    }
}
