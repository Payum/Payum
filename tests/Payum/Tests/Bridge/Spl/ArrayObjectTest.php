<?php
namespace Payum\Tests\Bridge\Spl;

use Payum\Bridge\Spl\ArrayObject;

class ArrayObjectTest extends \PHPUnit_Framework_TestCase 
{
    /**
     * @test
     */
    public function shouldBeSubClassOfArrayObject()
    {
        $rc = new \ReflectionClass('Payum\Bridge\Spl\ArrayObject');
        
        $this->assertTrue($rc->isSubclassOf('ArrayObject'));
    }

    /**
     * @test
     */
    public function shouldAllowGetPreviouslySetValueByIndex()
    {
        $array = new ArrayObject();
        $array['foo'] = 'bar';
        
        $this->assertTrue(isset($array['foo']));
        $this->assertEquals('bar', $array['foo']);
    }
    
    /**
     * @test
     */
    public function shouldAllowGetNullIfValueWithIndexNotSet()
    {
        $array = new ArrayObject();

        $this->assertFalse(isset($array['foo']));
        $this->assertNull($array['foo']);
    }
}