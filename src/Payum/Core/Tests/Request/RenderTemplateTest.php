<?php
namespace Payum\Core\Tests\Request;

use Payum\Core\Request\RenderTemplate;

class RenderTemplateTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function couldBeConstructedWithTemplateNameAndParametersAsArguments()
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
    public function shouldAllowGetParametersSetInConstructor()
    {
        $request = new RenderTemplate('aTemplate', array('foo' => 'fooVal', 'bar' => 'barVal'));

        $this->assertSame(array('foo' => 'fooVal', 'bar' => 'barVal'), $request->getParameters());
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

    public function provideParameters()
    {
        return array(
            array('foo', 'fooVal'),
            array('bar', 'barVal'),
        );
    }

    /**
     * @test
     * @dataProvider provideParameters
     *
     * @param string $name
     * @param mixed  $value
     */
    public function shouldAllowSetParameter($name, $value)
    {
        $request = new RenderTemplate('aTemplate', array());

        $this->assertFalse(array_key_exists($name, $request->getParameters()));

        $request->setParameter($name, $value);

        $result = $request->getParameters();
        $this->assertArrayHasKey($name, $result);
        $this->assertSame($value, $result[$name]);
    }

    /**
     * @test
     * @dataProvider provideParameters
     *
     * @param string $name
     * @param mixed  $value
     */
    public function shouldAllowAddParameter($name, $value)
    {
        $request = new RenderTemplate('aTemplate', array());

        $this->assertFalse(array_key_exists($name, $request->getParameters()));

        $request->addParameter($name, $value);

        $result = $request->getParameters();
        $this->assertArrayHasKey($name, $result);
        $this->assertSame($value, $result[$name]);
    }

    /**
     * @test
     */
    public function shouldAllowOverwriteExistingParameterOnSetParameter()
    {
        $request = new RenderTemplate('aTemplate', array());

        $request->setParameter('foo', 'fooVal');
        $request->setParameter('foo', 'barVal');

        $result = $request->getParameters();
        $this->assertSame('barVal', $result['foo']);
    }

    /**
     * @test
     * @expectedException \InvalidArgumentException
     */
    public function shouldThrowExceptionIfParameterExistsOnAddParameter()
    {
        $request = new RenderTemplate('aTemplate', array());

        $request->addParameter('foo', 'fooVal');
        $request->addParameter('foo', 'barVal');
    }
}
