<?php
namespace Payum\Core\Tests\Request;

use Payum\Core\Request\RenderTemplate;

class RenderTemplateTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function couldBeConstructedWithTemplateNameAndContextAsArguments()
    {
        new RenderTemplate('aTemplate', array());
    }

    /**
     * @test
     */
    public function shouldAllowGetTemplateNameSetInConstructor()
    {
        $request = new RenderTemplate('theTemplate', array());

        $this->assertSame('theTemplate', $request->getTemplateName());
    }

    /**
     * @test
     */
    public function shouldAllowGetContextSetInConstructor()
    {
        $request = new RenderTemplate('aTemplate', array('foo' => 'fooVal', 'bar' => 'barVal'));

        $this->assertSame(array('foo' => 'fooVal', 'bar' => 'barVal'), $request->getContext());
    }

    /**
     * @test
     */
    public function shouldAllowGetResultPreviouslySet()
    {
        $request = new RenderTemplate('aTemplate', array());

        $request->setResult('theResult');

        $this->assertEquals('theResult', $request->getResult());
    }
}
