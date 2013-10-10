<?php
namespace Payum\Bridge\Doctrine\Types;

use Doctrine\ODM\MongoDB\Types\Type;

/**
 * Used as a workaround till I (or you?) found out how to store object to mongo.
 *
 * More details:
 *     http://stackoverflow.com/questions/19196453/doctrine-can-i-use-mongo-hash-field-to-store-php-object
 *     http://stackoverflow.com/questions/19257660/proper-way-to-load-mongodb-hash-associated-array-mapping-when-not-using-annotati
 */
class ObjectType extends Type
{
    /**
     * {@inheritDoc}
     */
    public function convertToDatabaseValue($value)
    {
        return serialize($value);
    }

    /**
     * {@inheritDoc}
     */
    public function convertToPHPValue($value)
    {
        if ($value === null) {
            return null;
        }

        $value = (is_resource($value)) ? stream_get_contents($value) : $value;
        $val = unserialize($value);
        if ($val === false && $value !== 'b:0;') {
            throw new \LogicException('Conversion excpetion: ' . $value . '. ' . $this->getName());
        }

        return $val;
    }

    public function closureToMongo()
    {
        return '$return = serialize($value);';
    }

    public function closureToPHP()
    {
        return '$return = unserialize($value);';
    }
} 