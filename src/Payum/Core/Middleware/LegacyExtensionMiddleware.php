<?php

declare(strict_types=1);

namespace Payum\Core\Middleware;

use Exception;
use Payum\Core\Command\CommandInterface;
use Payum\Core\Gateway;
use Payum\Core\GatewayAwareInterface;
use Payum\Core\GatewayInterface;
use Payum\Core\Handler\Context;
use Payum\Core\Result\Result;

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
        if (! $this->gateway instanceof Gateway) {
            return $next($command, $context);
        }

        $extensions = $this->gateway->getExtensions();
        $legacyContext = $this->gateway->openExtensionContext($command);

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
            $this->gateway->closeExtensionContext();
        }
    }
}
