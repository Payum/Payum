<?php

namespace Payum\Core\Security;

interface CypherInterface
{
    /**
     * This method decrypts the passed value.
     *
     * @return string
     */
    public function decrypt(string $value);

    /**
     * This method encrypts the passed value.
     *
     * Binary data may be base64-encoded.
     *
     * @return string
     */
    public function encrypt(string $value);
}
