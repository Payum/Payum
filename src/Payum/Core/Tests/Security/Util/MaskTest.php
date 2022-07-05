<?php

namespace Payum\Core\Tests\Security\Util;

use Payum\Core\Security\Util\Mask;
use PHPUnit\Framework\TestCase;

class MaskTest extends TestCase
{
    public function provideValues(): \Iterator
    {
        yield 'just 16 numbers' => ['4567890123456789', '4XXXXXXXXXXX6789'];
        yield 'just 9 numbers' => ['498291842', '4XXXX1842'];
        yield 'numbers with dash' => ['3456-7890-1234-5678', '3XXX-XXXX-XXXX-5678'];
        yield 'numbers with a-z' => ['4928-abcd9012-3456', '4XXX-XXXXXXXX-3456'];
        yield 'english full name' => ['Mr. John Doe', 'MXX XXXX Doe'];
        yield 'german full name' => ['Günther Doe', 'GXXXXXX Doe'];
        yield 'russian full name' => ['Иван Петров', 'ИXXX XXтров'];
        yield 'short name' => ['Bea', 'BXX'];
        yield 'short name edge case' => ['Barbara', 'BXXXXXX'];
        yield 'short name that masked' => ['Beatrices', 'BXXXXices'];
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
