<?php
namespace Payum\Core\Tests\Security\Util;

use Payum\Core\Security\Util\Mask;
use PHPUnit\Framework\TestCase;

class MaskTest extends TestCase
{
    public static function provideValues(): \Iterator
    {
        yield 'just 16 numbers' => array("4567890123456789", "4XXXXXXXXXXX6789");
        yield 'just 9 numbers' => array("498291842", "4XXXX1842");
        yield 'numbers with dash' => array("3456-7890-1234-5678", "3XXX-XXXX-XXXX-5678");
        yield 'numbers with a-z' => array("4928-abcd9012-3456", "4XXX-XXXXXXXX-3456");
        yield 'english full name' => array("Mr. John Doe", "MXX XXXX Doe");
        yield 'german full name' => array("Günther Doe", "GXXXXXX Doe");
        yield 'russian full name' => array("Иван Петров", "ИXXX XXтров");
        yield 'short name' => array("Bea", "BXX");
        yield 'short name edge case' => array('Barbara', 'BXXXXXX');
        yield 'short name that masked' => array('Beatrices', 'BXXXXices');
    }

    /**
     * @dataProvider provideValues
     */
    public function testShouldAllowGenerateToken($value, $expected)
    {
        $this->assertSame($expected, Mask::mask($value));
    }

    public function testShouldAllowChangeMaskedSymbol()
    {
        $this->assertSame('1***-****-****-5678', Mask::mask('1234-5678-1234-5678', '*'));
    }

    public function testShouldAllowChangeNumberOfLastShownSymbols()
    {
        $this->assertSame('1XXX-XXXX-1234-5678', Mask::mask('1234-5678-1234-5678', null, 8));
    }

    public function testShouldNotShowAnythingIfNegativeShowLastGiven()
    {
        $this->assertSame('BXXXXXXXX', Mask::mask('Beatrices', null, -1));
    }
}
