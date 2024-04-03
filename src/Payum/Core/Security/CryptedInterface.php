<?php

namespace Payum\Core\Security;

interface CryptedInterface
{
    public function decrypt(CypherInterface $cypher);

    public function encrypt(CypherInterface $cypher);
}
