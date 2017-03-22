<?php
namespace Payum\Core\Security;

interface CryptedInterface
{
    /**
     * {@inheritdoc}
     */
    public function decrypt(CypherInterface $cypher);

    /**
     * {@inheritdoc}
     */
    public function encrypt(CypherInterface $cypher);
}
