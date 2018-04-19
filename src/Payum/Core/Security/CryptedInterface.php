<?php
namespace Payum\Core\Security;

interface CryptedInterface
{
    /**
     * @param CypherInterface $cypher
     *
     * @return void
     */
    public function decrypt(CypherInterface $cypher);

    /**
     * @param CypherInterface $cypher
     *
     * @return void
     */
    public function encrypt(CypherInterface $cypher);
}
