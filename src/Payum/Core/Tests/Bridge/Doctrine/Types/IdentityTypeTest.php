<?php

declare(strict_types=1);

namespace Payum\Core\Tests\Bridge\Doctrine\Types;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\ConversionException;
use Doctrine\DBAL\Types\Type;
use Payum\Core\Bridge\Doctrine\Types\IdentityType;
use Payum\Core\Model\Identity;
use PHPUnit\Framework\TestCase;
use stdClass;
use Stringable;

final class IdentityTypeTest extends TestCase
{
    public function testShouldRegisterOnce(): void
    {
        IdentityType::register();
        IdentityType::register();

        $this->assertInstanceOf(IdentityType::class, Type::getType(IdentityType::NAME));
    }

    public function testShouldStoreClassAndIdAsJson(): void
    {
        $this->assertSame(
            '{"class":"stdClass","id":123}',
            (new IdentityType())->convertToDatabaseValue(new Identity(123, stdClass::class), $this->platform())
        );
    }

    public function testShouldReadBackWhatItWrote(): void
    {
        $type = new IdentityType();
        $identity = new Identity('an/ïd', stdClass::class);

        $this->assertEquals($identity, $type->convertToPHPValue($type->convertToDatabaseValue($identity, $this->platform()), $this->platform()));
    }

    public function testShouldStoreAStringableIdAsAString(): void
    {
        $id = new class() implements Stringable {
            public function __toString(): string
            {
                return '0193f6b4-5c6b-7000-8000-000000000000';
            }
        };

        $this->assertSame(
            '{"class":"stdClass","id":"0193f6b4-5c6b-7000-8000-000000000000"}',
            (new IdentityType())->convertToDatabaseValue(new Identity($id, stdClass::class), $this->platform())
        );
    }

    public function testThrowsOnAnIdThatCannotBeStored(): void
    {
        $this->expectException(ConversionException::class);

        (new IdentityType())->convertToDatabaseValue(new Identity(['a', 'b'], stdClass::class), $this->platform());
    }

    public function testThrowsOnAValueThatIsNotAnIdentity(): void
    {
        $this->expectException(ConversionException::class);

        (new IdentityType())->convertToDatabaseValue(new stdClass(), $this->platform());
    }

    public function testShouldReadAnEmptyValueAsNull(): void
    {
        $this->assertNull((new IdentityType())->convertToPHPValue('', $this->platform()));
    }

    public function testThrowsOnJsonThatIsNotAnIdentity(): void
    {
        $this->expectException(ConversionException::class);

        (new IdentityType())->convertToPHPValue('{"foo":"bar"}', $this->platform());
    }

    public function testThrowsOnAValueThatIsNotJson(): void
    {
        $this->expectException(ConversionException::class);

        (new IdentityType())->convertToPHPValue('O:8:"stdClass":0:{}', $this->platform());
    }

    private function platform(): AbstractPlatform
    {
        return $this->createStub(AbstractPlatform::class);
    }
}
