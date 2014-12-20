<?php
namespace Payum\Core\Tests\Security;

use Payum\Core\Model\Token;
use Payum\Core\Security\PlainHttpRequestVerifier;

class PlainHttpRequestVerifierTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldImplementHttpRequestVerifierInterface()
    {
        $rc = new \ReflectionClass('Payum\Core\Security\PlainHttpRequestVerifier');

        $this->assertTrue($rc->implementsInterface('Payum\Core\Security\HttpRequestVerifierInterface'));
    }

    /**
     * @test
     */
    public function couldBeConstructedWithTokenStorageAsFirstArgument()
    {
        new PlainHttpRequestVerifier($this->createStorageMock());
    }

    /**
     * @test
     *
     * @expectedException \Payum\Core\Exception\InvalidArgumentException
     * @expectedExceptionMessage Invalid request given. In most cases you have to pass $_REQUEST array.
     */
    public function throwIfRequestIsNotArrayOnVerify()
    {
        $verifier = new PlainHttpRequestVerifier($this->createStorageMock());

        $verifier->verify('not array');
    }

    /**
     * @test
     *
     * @expectedException \Payum\Core\Exception\InvalidArgumentException
     * @expectedExceptionMessage Token parameter `payum_token` was not found in in the http request.
     */
    public function throwIfRequestNotContainTokenParameterOnVerify()
    {
        $verifier = new PlainHttpRequestVerifier($this->createStorageMock());

        $verifier->verify(array());
    }

    /**
     * @test
     *
     * @expectedException \Payum\Core\Exception\InvalidArgumentException
     * @expectedExceptionMessage A token with hash `invalidHash` could not be found.
     */
    public function throwIfStorageCouldNotFindTokenByGivenHashOnVerify()
    {
        $invalidHash = 'invalidHash';

        $storageMock = $this->createStorageMock();
        $storageMock
            ->expects($this->once())
            ->method('find')
            ->with($invalidHash)
            ->will($this->returnValue(null))
        ;

        $verifier = new PlainHttpRequestVerifier($storageMock);

        $verifier->verify(array('payum_token' => $invalidHash));
    }

    /**
     * @test
     *
     * @expectedException \Payum\Core\Exception\InvalidArgumentException
     * @expectedExceptionMessage The current url http://target.com/bar not match target url http://target.com/foo set in the token.
     */
    public function throwIfTargetUrlPathNotMatchServerRequestUriPathOnVerify()
    {
        $_SERVER['REQUEST_URI'] = 'http://target.com/bar';

        $token = new Token();
        $token->setHash('theHash');
        $token->setTargetUrl('http://target.com/foo');

        $storageMock = $this->createStorageMock();
        $storageMock
            ->expects($this->once())
            ->method('find')
            ->with('theHash')
            ->will($this->returnValue($token))
        ;

        $verifier = new PlainHttpRequestVerifier($storageMock);

        $verifier->verify(array('payum_token' => 'theHash'));
    }

    /**
     * @test
     */
    public function shouldReturnExpectedTokenIfAllCheckPassedOnVerify()
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
            ->will($this->returnValue($expectedToken))
        ;

        $verifier = new PlainHttpRequestVerifier($storageMock);

        $actualToken = $verifier->verify(array('payum_token' => 'theHash'));

        $this->assertSame($expectedToken, $actualToken);
    }

    /**
     * @test
     */
    public function shouldReturnTokenObjectSetToRequestGlobalArrayWithoutChecks()
    {
        $expectedToken = new Token();

        $verifier = new PlainHttpRequestVerifier($this->createStorageMock());

        $actualToken = $verifier->verify(array('payum_token' => $expectedToken));

        $this->assertSame($expectedToken, $actualToken);
    }

    /**
     * @test
     */
    public function shouldAllowCustomizeTokenParameterInConstructor()
    {
        $expectedToken = new Token();

        $verifier = new PlainHttpRequestVerifier($this->createStorageMock(), 'custom_token');

        $actualToken = $verifier->verify(array('custom_token' => $expectedToken));

        $this->assertSame($expectedToken, $actualToken);
    }

    /**
     * @test
     */
    public function shouldCallStorageDeleteModelMethodOnInvalidate()
    {
        $token = new Token();

        $storageMock = $this->createStorageMock();
        $storageMock
            ->expects($this->once())
            ->method('delete')
            ->with($this->identicalTo($token))
        ;

        $verifier = new PlainHttpRequestVerifier($storageMock);

        $verifier->invalidate($token);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|\Payum\Core\Storage\StorageInterface
     */
    protected function createStorageMock()
    {
        return $this->getMock('Payum\Core\Storage\StorageInterface');
    }
}
