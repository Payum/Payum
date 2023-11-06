<?php
namespace Payum\Core\Tests\Request;

use Payum\Core\Request\RenderTemplate;
use PHPUnit\Framework\TestCase;

class RenderTemplateTest extends TestCase
{
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

    public static function provideParameters(): \Iterator
    {
        yield array('foo', 'fooVal');
        yield array('bar', 'barVal');
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

        $this->assertArrayNotHasKey($name, $request->getParameters());

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

        $this->assertArrayNotHasKey($name, $request->getParameters());

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
     */
    public function shouldThrowExceptionIfParameterExistsOnAddParameter()
    {
        $this->expectException(\InvalidArgumentException::class);
        $request = new RenderTemplate('aTemplate', array());

        $request->addParameter('foo', 'fooVal');
        $request->addParameter('foo', 'barVal');
    }
}
