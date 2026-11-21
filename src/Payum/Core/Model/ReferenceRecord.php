<?php

declare(strict_types=1);

namespace Payum\Core\Model;

use Payum\Core\Storage\IdentityInterface;

/**
 * Ties a reference a PSP uses, such as a subscription id, to the Payum subject it belongs to.
 */
class ReferenceRecord
{
    protected string $key;

    protected string $namespace;

    protected string $reference;

    protected string $gatewayName;

    protected string $subjectClass;

    protected string $subjectId;

    public static function keyFor(string $namespace, string $reference): string
    {
        return hash('sha256', $namespace . "\0" . $reference);
    }

    public function populate(string $namespace, string $reference, string $gatewayName, IdentityInterface $subject): void
    {
        $this->key = self::keyFor($namespace, $reference);
        $this->namespace = $namespace;
        $this->reference = $reference;
        $this->gatewayName = $gatewayName;
        $this->subjectClass = $subject->getClass();
        $this->subjectId = (string) $subject->getId();
    }

    public function getKey(): string
    {
        return $this->key;
    }

    /**
     * @param string $key a hash of the namespace and the reference
     */
    public function setKey(string $key): void
    {
        $this->key = $key;
    }

    public function getNamespace(): string
    {
        return $this->namespace;
    }

    public function setNamespace(string $namespace): void
    {
        $this->namespace = $namespace;
    }

    public function getReference(): string
    {
        return $this->reference;
    }

    public function setReference(string $reference): void
    {
        $this->reference = $reference;
    }

    public function getGatewayName(): string
    {
        return $this->gatewayName;
    }

    public function setGatewayName(string $gatewayName): void
    {
        $this->gatewayName = $gatewayName;
    }

    public function getSubjectClass(): string
    {
        return $this->subjectClass;
    }

    public function setSubjectClass(string $subjectClass): void
    {
        $this->subjectClass = $subjectClass;
    }

    public function getSubjectId(): string
    {
        return $this->subjectId;
    }

    public function setSubjectId(string $subjectId): void
    {
        $this->subjectId = $subjectId;
    }

    public function getIdentity(): IdentityInterface
    {
        return new Identity($this->subjectId, $this->subjectClass);
    }
}
