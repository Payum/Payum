<?php
namespace Payum\Bundle\PayumBundle\Tests\Action;

use Payum\Bundle\PayumBundle\Action\GetHttpQueryAction;
use Payum\Core\Request\GetHttpQueryRequest;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\HttpFoundation\Request;

class GetHttpQueryActionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldBeSubClassOfContainerAware()
    {
        $rc = new \ReflectionClass('Payum\Bundle\PayumBundle\Action\GetHttpQueryAction');

        $this->assertTrue($rc->isSubclassOf('Symfony\Component\DependencyInjection\ContainerAware'));
    }

    /**
     * @test
     */
    public function shouldImplementsActionInterface()
    {
        $rc = new \ReflectionClass('Payum\Bundle\PayumBundle\Action\GetHttpQueryAction');

        $this->assertTrue($rc->isSubclassOf('Payum\Core\Action\ActionInterface'));
    }

    /**
     * @test
     */
    public function couldBeConstructedWithoutAnyArguments()
    {
        new GetHttpQueryAction;
    }

    /**
     * @test
     */
    public function shouldSupportGetHttpQueryRequest()
    {
        $action = new GetHttpQueryAction;

        $this->assertTrue($action->supports(new GetHttpQueryRequest));
    }

    /**
     * @test
     */
    public function shouldNotSupportAnythingNotGetHttpQueryRequest()
    {
        $action = new GetHttpQueryAction;

        $this->assertFalse($action->supports(new \stdClass));
    }

    /**
     * @test
     *
     * @expectedException \Payum\Core\Exception\RequestNotSupportedException
     * @expectedExceptionMessage Action GetHttpQueryAction is not supported the request stdClass.
     */
    public function throwIfNotSupportedRequestedPassOnExecute()
    {
        $action = new GetHttpQueryAction;

        $action->execute(new \stdClass);
    }

    /**
     * @test
     */
    public function shouldDoNothingIfContainerNotHaveRequestService()
    {
        $action = new GetHttpQueryAction;
        $action->setContainer(new Container);

        $getHttpQuery = new GetHttpQueryRequest;

        $action->execute($getHttpQuery);

        $this->assertCount(0, iterator_to_array($getHttpQuery));
    }

    /**
     * @test
     */
    public function shouldFillGetHttpQueryRequestWithQueryParametersFromHttpRequestOnExecute()
    {
        $request = Request::create('/', 'GET', array(
            'foo' => 'fooVal',
            'bar' => 'barVal',
        ));

        $container = new Container;
        $container->set('request', $request);

        $action = new GetHttpQueryAction;
        $action->setContainer($container);

        $getHttpQuery = new GetHttpQueryRequest;

        $action->execute($getHttpQuery);

        $this->assertCount(2, iterator_to_array($getHttpQuery));

        $this->assertEquals('fooVal', $getHttpQuery['foo']);
        $this->assertEquals('barVal', $getHttpQuery['bar']);
    }
} 