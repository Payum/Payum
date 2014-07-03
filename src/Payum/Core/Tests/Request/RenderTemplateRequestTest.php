<?php
namespace Payum\Core\Tests\Request;

use Payum\Core\Request\RenderTemplateRequest;

class RenderTemplateRequestTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function couldBeConstructedWithTemplateNameAndContextAsArguments()
    {
        new RenderTemplateRequest('aTemplate', array());
    }

    /**
     * @test
     */
    public function shouldAllowGetTemplateNameSetInConstructor()
    {
        $request = new RenderTemplateRequest('theTemplate', array());

        $this->assertSame('theTemplate', $request->getTemplateName());
    }

    /**
     * @test
     */
    public function shouldAllowGetContextSetInConstructor()
    {
        $request = new RenderTemplateRequest('aTemplate', array('foo' => 'fooVal', 'bar' => 'barVal'));

        $this->assertSame(array('foo' => 'fooVal', 'bar' => 'barVal'), $request->getContext());
    }

    /**
     * @test
     */
    public function shouldAllowGetResultPreviouslySet()
    {
        $request = new RenderTemplateRequest('aTemplate', array());

        $request->setResult('theResult');

        $this->assertEquals('theResult', $request->getResult());
    }
}