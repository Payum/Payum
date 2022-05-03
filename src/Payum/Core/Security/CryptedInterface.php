<?php
namespace Payum\Core\Security;

interface CryptedInterface
{
    public function decrypt(CypherInterface $cypher): void;

    public function encrypt(CypherInterface $cypher): void;
}
