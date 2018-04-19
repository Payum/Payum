<?php
namespace Payum\Core\Security;

interface CypherInterface
{
    /**
     * This method decrypts the passed value.
     *
     * @param string $value
     *
     * @return string
     */
    public function decrypt($value);

    /**
     * This method encrypts the passed value.
     *
     * Binary data may be base64-encoded.
     *
     * @param string $value
     *
     * @return string
     */
    public function encrypt($value);
}
