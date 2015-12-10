<?php
namespace Payum\Core\Tests\Security\Util;

use Payum\Core\Security\Util\Mask;

class MaskTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function provideValues()
    {
        return array(
            'just 16 numbers' => array("4567890123456789", "4XXXXXXXXXXX6789"),
            'just 9 numbers' => array("498291842", "4XXXX1842"),
            'numbers with dash' => array("3456-7890-1234-5678", "3XXX-XXXX-XXXX-5678"),
            'numbers with a-z' => array("4928-abcd9012-3456", "4XXX-XXXXXXXX-3456"),
            'english full name' => array("Mr. John Doe", "MXX XXXX Doe"),
            'german full name' => array("Günther Doe", "GXXXXXX Doe"),
            'russian full name' => array("Иван Петров", "ИXXX XXтров"),
            'short name' => array("Bea", "BXX"),
            'short name edge case' => array('Barbara', 'BXXXXXX'),
            'short name that masked' => array('Beatrices', 'BXXXXices'),
        );
    }

    /**
     * @test
     *
     * @dataProvider provideValues
     */
    public function shouldAllowGenerateToken($value, $expected)
    {
        $this->assertSame($expected, Mask::mask($value));
    }

    /**
     * @test
     */
    public function shouldAllowChangeMaskedSymbol()
    {
        $this->assertSame('1***-****-****-5678', Mask::mask('1234-5678-1234-5678', '*'));
    }

    /**
     * @test
     */
    public function shouldAllowChangeNumberOfLastShownSymbols()
    {
        $this->assertSame('1XXX-XXXX-1234-5678', Mask::mask('1234-5678-1234-5678', null, 8));
    }

    /**
     * @test
     */
    public function shouldNotShowAnythingIfNegativeShowLastGiven()
    {
        $this->assertSame('BXXXXXXXX', Mask::mask('Beatrices', null, -1));
    }
}
