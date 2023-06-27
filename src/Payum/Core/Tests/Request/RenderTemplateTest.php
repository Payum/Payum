<?php

namespace Payum\Core\Tests\Request;

use InvalidArgumentException;
use Iterator;
use Payum\Core\Request\RenderTemplate;
use PHPUnit\Framework\TestCase;

class RenderTemplateTest extends TestCase
{
    public function testShouldAllowGetTemplateNameSetInConstructor(): void
    {
        $request = new RenderTemplate('theTemplate', []);

        $this->assertSame('theTemplate', $request->getTemplateName());
    }

    public function testShouldAllowGetParametersSetInConstructor(): void
    {
        $request = new RenderTemplate('aTemplate', [
            'foo' => 'fooVal',
            'bar' => 'barVal',
        ]);

        $this->assertSame([
            'foo' => 'fooVal',
            'bar' => 'barVal',
        ], $request->getParameters());
    }

    public function testShouldAllowGetResultPreviouslySet(): void
    {
        $request = new RenderTemplate('aTemplate', []);

        $request->setResult('theResult');

        $this->assertSame('theResult', $request->getResult());
    }

    public function provideParameters(): Iterator
    {
        yield ['foo', 'fooVal'];
        yield ['bar', 'barVal'];
    }

    /**
     * @dataProvider provideParameters
     */
    public function testShouldAllowSetParameter(string $name, string $value): void
    {
        $request = new RenderTemplate('aTemplate', []);

        $this->assertArrayNotHasKey($name, $request->getParameters());

        $request->setParameter($name, $value);

        $result = $request->getParameters();
        $this->assertArrayHasKey($name, $result);
        $this->assertSame($value, $result[$name]);
    }

    /**
     * @dataProvider provideParameters
     */
    public function testShouldAllowAddParameter(string $name, string $value): void
    {
        $request = new RenderTemplate('aTemplate', []);

        $this->assertArrayNotHasKey($name, $request->getParameters());

        $request->addParameter($name, $value);

        $result = $request->getParameters();
        $this->assertArrayHasKey($name, $result);
        $this->assertSame($value, $result[$name]);
    }

    public function testShouldAllowOverwriteExistingParameterOnSetParameter(): void
    {
        $request = new RenderTemplate('aTemplate', []);

        $request->setParameter('foo', 'fooVal');
        $request->setParameter('foo', 'barVal');

        $result = $request->getParameters();
        $this->assertSame('barVal', $result['foo']);
    }

    public function testShouldThrowExceptionIfParameterExistsOnAddParameter(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $request = new RenderTemplate('aTemplate', []);

        $request->addParameter('foo', 'fooVal');
        $request->addParameter('foo', 'barVal');
    }
}
