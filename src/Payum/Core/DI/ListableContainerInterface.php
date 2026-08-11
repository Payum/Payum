<?php

namespace Payum\Core\DI;

use Psr\Container\ContainerInterface;

/**
 * A container which is able to tell which entries it holds.
 *
 * PSR-11 has no way of listing the entries of a container, so Payum can only turn the services of a
 * container it is given into definitions of the gateway containers when that container is able to
 * report them. Implement this interface to have the services of your own container - a framework
 * container for instance - injected into the actions of a gateway.
 *
 * @see FallbackContainer
 */
interface ListableContainerInterface extends ContainerInterface
{
    /**
     * The ids of every entry this container can resolve.
     *
     * @return list<string>
     */
    public function getKnownEntryNames(): array;
}
