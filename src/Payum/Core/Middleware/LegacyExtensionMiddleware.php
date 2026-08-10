<?php

declare(strict_types=1);

namespace Payum\Core\Middleware;

use Exception;
use Payum\Core\Command\CommandInterface;
use Payum\Core\Extension\Context as ExtensionContext;
use Payum\Core\Gateway;
use Payum\Core\GatewayAwareInterface;
use Payum\Core\GatewayInterface;
use Payum\Core\Handler\Context;
use Payum\Core\Result\Result;
use function array_pop;

/**
 * Runs the gateway's registered extensions around a command.
 *
 * Extensions predate commands, so the bridge is one-way: they observe, and they can abort by throwing,
 * but they cannot swap the result the way they can swap a reply — a Result is not a ReplyInterface, so
 * Context::setReply() has nothing to accept. getAction() is likewise always null, since a command is
 * answered by a handler.
 *
 * The collection is read on every execution rather than captured once, because extensions are added to a
 * gateway after it is built — the registry adds a storage extension when the gateway is first fetched.
 */
final class LegacyExtensionMiddleware implements MiddlewareInterface, GatewayAwareInterface, HasPriority
{
    private ?Gateway $gateway = null;

    /**
     * Mirrors the nesting an extension would see on the action path. Held here because these contexts are
     * this middleware's own, not the executor's.
     *
     * @var list<ExtensionContext>
     */
    private array $stack = [];

    public static function priority(): int
    {
        return 500;
    }

    public function setGateway(GatewayInterface $gateway): void
    {
        $this->gateway = $gateway instanceof Gateway ? $gateway : null;
    }

    public function process(CommandInterface $command, Context $context, callable $next): Result
    {
        if (null === $this->gateway) {
            return $next($command, $context);
        }

        $extensions = $this->gateway->getExtensions();
        $legacyContext = new ExtensionContext($this->gateway, $command, $this->stack);
        $this->stack[] = $legacyContext;

        try {
            $extensions->onPreExecute($legacyContext);
            $extensions->onExecute($legacyContext);

            $result = $next($command, $context);

            $extensions->onPostExecute($legacyContext);

            return $result;
        } catch (Exception $exception) {
            $legacyContext->setException($exception);
            $extensions->onPostExecute($legacyContext);

            throw $exception;
        } finally {
            array_pop($this->stack);
        }
    }
}
