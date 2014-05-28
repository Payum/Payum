<?php
namespace Payum\Core\Tests\Security\Util;

use Payum\Core\Security\Util\Mask;

class MaskTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function provideCreditCardToMask()
    {
        return array(
            'just numbers 16' => array("4567890123456789", "4XXXXXXXXXXX6789"),
            'just numbers 9' => array("498291842", "4XXXX1842"),
            'with dash' => array("3456-7890-1234-5678", "3XXX-XXXX-XXXX-5678"),
            'with a-z' => array("4928-abcd9012-3456", "4XXX-XXXXXXXX-3456"),
        );
    }

    /**
     * @test
     *
     * @dataProvider provideCreditCardToMask
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
} 