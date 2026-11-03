<?php

declare(strict_types=1);

namespace Payum\Core\Bridge\Doctrine\Types;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\ConversionException;
use Doctrine\DBAL\Types\Type;

/**
 * Reads and writes the column format of DBAL 3's built-in `object` type, which DBAL 4 removed.
 */
final class DbalObjectType extends Type
{
    public const NAME = 'object';

    public static function register(): void
    {
        if (! Type::hasType(self::NAME)) {
            Type::addType(self::NAME, self::class);
        }
    }

    /**
     * @param array<string, mixed> $column
     */
    public function getSQLDeclaration(array $column, AbstractPlatform $platform): string
    {
        return $platform->getClobTypeDeclarationSQL($column);
    }

    public function convertToDatabaseValue(mixed $value, AbstractPlatform $platform): string
    {
        return serialize($value);
    }

    public function convertToPHPValue(mixed $value, AbstractPlatform $platform): mixed
    {
        if (null === $value) {
            return null;
        }

        $value = is_resource($value) ? stream_get_contents($value) : $value;

        set_error_handler(static function (int $code, string $message): bool {
            throw new ConversionException('Could not convert database value to "' . self::NAME . '": ' . $message);
        });

        try {
            return unserialize($value);
        } finally {
            restore_error_handler();
        }
    }

    public function getName(): string
    {
        return self::NAME;
    }
}
