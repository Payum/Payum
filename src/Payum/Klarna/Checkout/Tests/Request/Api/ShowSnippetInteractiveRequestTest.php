<?php
namespace Payum\Klarna\Checkout\Tests\Request\Api;

use Payum\Klarna\Checkout\Request\Api\ShowSnippetInteractiveRequest;

class ShowSnippetInteractiveRequestTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldBeSubClassOfBaseInteractiveRequest()
    {
        $rc = new \ReflectionClass('Payum\Klarna\Checkout\Request\Api\ShowSnippetInteractiveRequest');

        $this->assertTrue($rc->isSubclassOf('Payum\Core\Request\BaseInteractiveRequest'));
    }

    /**
     * @test
     */
    public function couldBeConstructedWithSnippetAsArgument()
    {
        new ShowSnippetInteractiveRequest('aSnippet');
    }

    /**
     * @test
     */
    public function shouldAllowGetSnippetSetInConstructor()
    {
        $request = new ShowSnippetInteractiveRequest('theSnippet');

        $this->assertEquals('theSnippet', $request->getSnippet());
    }
}