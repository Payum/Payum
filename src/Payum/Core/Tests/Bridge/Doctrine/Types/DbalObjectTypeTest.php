<?php

declare(strict_types=1);

namespace Payum\Core\Tests\Bridge\Doctrine\Types;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\ConversionException;
use Doctrine\DBAL\Types\Type;
use Payum\Core\Bridge\Doctrine\Types\DbalObjectType;
use Payum\Core\Model\Identity;
use PHPUnit\Framework\TestCase;
use stdClass;

final class DbalObjectTypeTest extends TestCase
{
    public function testShouldMakeTheObjectTypeAvailable(): void
    {
        DbalObjectType::register();
        DbalObjectType::register();

        $this->assertTrue(Type::hasType(DbalObjectType::NAME));
    }

    public function testShouldReadBackWhatItWrote(): void
    {
        $type = new DbalObjectType();
        $identity = new Identity('anId', stdClass::class);

        $stored = $type->convertToDatabaseValue($identity, $this->platform());

        $this->assertSame(serialize($identity), $stored);
        $this->assertEquals($identity, $type->convertToPHPValue($stored, $this->platform()));
    }

    public function testShouldReadAStream(): void
    {
        $stream = fopen('php://memory', 'r+');
        fwrite($stream, serialize([
            'foo' => 'bar',
        ]));
        rewind($stream);

        $this->assertSame([
            'foo' => 'bar',
        ], (new DbalObjectType())->convertToPHPValue($stream, $this->platform()));
    }

    public function testShouldReadNullAsNull(): void
    {
        $this->assertNull((new DbalObjectType())->convertToPHPValue(null, $this->platform()));
    }

    public function testShouldReadSerializedFalse(): void
    {
        $this->assertFalse((new DbalObjectType())->convertToPHPValue(serialize(false), $this->platform()));
    }

    public function testThrowsOnAValueThatIsNotSerialized(): void
    {
        $this->expectException(ConversionException::class);

        (new DbalObjectType())->convertToPHPValue('not serialized', $this->platform());
    }

    public function testShouldStoreInATextColumn(): void
    {
        $platform = $this->createMock(AbstractPlatform::class);
        $platform
            ->expects($this->once())
            ->method('getClobTypeDeclarationSQL')
            ->with([
                'name' => 'details',
            ])
            ->willReturn('CLOB')
        ;

        $this->assertSame('CLOB', (new DbalObjectType())->getSQLDeclaration([
            'name' => 'details',
        ], $platform));
    }

    private function platform(): AbstractPlatform
    {
        return $this->createStub(AbstractPlatform::class);
    }
}
