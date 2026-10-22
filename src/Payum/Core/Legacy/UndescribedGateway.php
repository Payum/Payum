<?php

declare(strict_types=1);

namespace Payum\Core\Legacy;

use League\Uri\Uri;
use Payum\Core\Exception\LogicException;
use Payum\Core\Gateway\GatewayInterface;
use Payum\Core\Metadata\Logo;
use function sprintf;

/**
 * What a handler's Context says about a gateway that a 1.x factory built, which describes nothing but its
 * name.
 *
 * @deprecated since 2.0, removed in 3.0 along with gateway factories.
 */
final class UndescribedGateway implements GatewayInterface
{
    public function __construct(
        private readonly ?string $name = null,
    ) {
    }

    public function configClass(): string
    {
        throw $this->undescribed('a config class');
    }

    public function handlers(): array
    {
        return [];
    }

    public function logo(): Logo
    {
        throw $this->undescribed('a logo');
    }

    public function name(): string
    {
        return $this->name ?? '';
    }

    public function websiteUrl(): Uri
    {
        throw $this->undescribed('a website');
    }

    private function undescribed(string $what): LogicException
    {
        return new LogicException(sprintf(
            'Gateway "%s" is built by a 1.x gateway factory, which has no %s to give. Describe it with a %s.',
            $this->name,
            $what,
            GatewayInterface::class,
        ));
    }
}
